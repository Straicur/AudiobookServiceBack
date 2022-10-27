<?php

namespace App\Service;


use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class AudiobookService
{
    private MP3FileService $MP3FileService;
    private AudiobooksID3TagsReaderService $audiobooksID3TagsReaderService;

    /**
     * @param MP3FileService $MP3FileService
     * @param AudiobooksID3TagsReaderService $audiobooksID3TagsReaderService
     */
    public function __construct(MP3FileService $MP3FileService, AudiobooksID3TagsReaderService $audiobooksID3TagsReaderService)
    {
        $this->MP3FileService = $MP3FileService;
        $this->audiobooksID3TagsReaderService = $audiobooksID3TagsReaderService;
    }

    /**
     * @param $object
     * @param $setName
     * @return bool
     */
    public function fileExists($object, $setName): bool
    {
        $fsObject = new Filesystem();

        $whole_dir_path = $_ENV['MAIN_DIR'] . "/" . $setName . "/" . $object->hash_name;

        if (!$fsObject->exists($whole_dir_path)) {
            $old = umask(0);
            $fsObject->mkdir($whole_dir_path, 0775);
            umask($old);
        }

        $file = $whole_dir_path . "/" . $object->hash_name . $object->part_nr;

        if (!$fsObject->exists($file)) {
            return true;
        } elseif ($fsObject->exists($file)) {
            return false;
        } else {
            return false;
        }
    }

    /**
     * @param $object
     * @param $setName
     * @return bool
     */
    public function lastFile($object, $setName): bool
    {
        $whole_dir_path = $_ENV['MAIN_DIR'] . "/" . $setName . "/" . $object->hash_name;

        $amountOfFiles = 0;

        if ($handle = opendir($whole_dir_path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $amountOfFiles += 1;
                }
            }
            closedir($handle);
        }

        if ($amountOfFiles == $object->all_parts_nr) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $object
     * @param $setName
     * @return false|string
     */
    public function combineFiles($object, $setName): bool|string
    {
        try {

            $finalFile = $_ENV['MAIN_DIR'] . "/" . $setName . "/" . $object->file_name . ".zip";

            $zipFile = fopen($finalFile, "a");
            $path = $_ENV['MAIN_DIR'] . "/" . $setName . "/" . $object->hash_name;

            $zipfiles = array_diff(scandir($path), array('.', '..'));
            $result = [];

            foreach ($zipfiles as $file) {
                $hash = strlen($object->hash_name);
                array_push($result, substr($file, $hash));
            }
            sort($result);

            foreach ($result as $file) {

                $partFile = fopen($_ENV['MAIN_DIR'] . "/" . $setName . "/" . $object->hash_name . "/" . $object->hash_name . $file, "r");

                $readData = fread($partFile, filesize($_ENV['MAIN_DIR'] . "/" . $setName . "/" . $object->hash_name . "/" . $object->hash_name . $file));

                fwrite($zipFile, base64_decode($readData, true));

                fclose($partFile);

            }

            fclose($zipFile);

            $it = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it,
                RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }

            if (rmdir($path)) {
                return $finalFile;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            print_r($e);
            return false;
        }
    }

    /**
     * @param $file_name
     * @param $setName
     * @return array
     */
    public function createAudiobookJsonData($file_name, $setName): array
    {
        $id3Data = [];
        $mp3Size = 0;
        $mp3Duration = "";
        $parts = 0;
        if ($handle = opendir($_ENV['MAIN_DIR'] . "/" . $setName . "/" . $file_name)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $file_parts = pathinfo($entry);
                    if ($file_parts['extension'] == "mp3") {
                        $mp3file = $entry;
                        if ($mp3file !== "") {

                            $parts++;

                            $mp3Dir = $_ENV['MAIN_DIR'] . "/" . $setName . "/" . $file_name . "/" . $mp3file;

                            $mp3Duration = $this->MP3FileService->getDuration();

                            $mp3Size = $mp3Size + filesize($mp3Dir);

                            $id3TrackData = $this->audiobooksID3TagsReaderService->getTagsInfo($mp3Dir);

                            if (empty($id3Data)) {
                                foreach ($id3TrackData as $key => $index) {
                                    $id3Data[$key] = $index;
                                }
                            } else {
                                $sameKeys = array_intersect_key($id3Data, $id3TrackData);

                                $keys = array_keys($id3TrackData);

                                foreach ($keys as $key => $index) {
                                    if (array_key_exists($key, $sameKeys)) {
                                        continue;
                                    } else {
                                        $id3Data[$index] = $id3TrackData[$index];
                                    }
                                }
                            }
                        }
                    }
                }
            }
            closedir($handle);
        }

        $id3Data['duration'] = $mp3Duration;
        $id3Data['size'] = number_format($mp3Size / 1048576, 2);
        $id3Data['parts'] = $parts;
        $id3Data['title'] = $file_name;

        return ($id3Data);

    }

    /**
     * @param $object
     * @param $setName
     * @param $fileName
     * @return bool|array
     */
    public function unzip($object, $setName, $fileName): bool|array
    {
        $file = $_ENV['MAIN_DIR'] . "/" . $setName . "/" . $object->file_name . ".zip";
        $path = $_ENV['MAIN_DIR'] . "/" . $setName;

        $zip = new ZipArchive;
        $res = $zip->open($fileName);

        if ($res === TRUE) {

            $dir = trim($zip->getNameIndex(0), '/');

            $zip->extractTo($path);
            $zip->close();
            unlink($file);

            rename($_ENV['MAIN_DIR'] . "/" . $setName . "/" . $dir, $_ENV['MAIN_DIR'] . "/" . $setName . "/" . $object->file_name);

            return $this->createAudiobookJsonData($object->file_name, $setName);
        } else {
            return false;
        }
    }
}