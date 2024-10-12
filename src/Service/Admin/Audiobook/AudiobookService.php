<?php

declare(strict_types=1);

namespace App\Service\Admin\Audiobook;

use App\Exception\AudiobookConfigServiceException;
use App\Exception\DataNotFoundException;
use App\Query\Admin\AdminAudiobookAddFileInterface;
use App\Service\TranslateService;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;
use ZipArchive;

class AudiobookService implements AudiobookServiceInterface
{
    private AdminAudiobookAddFileInterface|null $query = null;
    private string $whole_dir_path = '';
    private string $whole_zip_path = '';

    public function __construct(
        private readonly AudiobooksID3TagsReaderService $audiobooksID3TagsReaderService,
        private readonly TranslateService $translateService,
    ) {
    }

    public function configure(AdminAudiobookAddFileInterface $query): void
    {
        $this->query = $query;
        $this->whole_dir_path = $_ENV['MAIN_DIR'] . '/' . $this->query->getHashName();
        $this->whole_zip_path = $_ENV['MAIN_DIR'] . '/' . $this->query->getFileName();
    }

    public function checkAndAddFile(): void
    {
        $this->checkConfiguration();

        $fsObject = new Filesystem();

        $size = $this->checkSystemStorage($_ENV['MAIN_DIR']);

        $this->checkOrCreateAudiobookFolder($fsObject);

        if ($size >= (int)$_ENV['INSTITUTION_VOLUMEN']) {
            $this->removeFolder($this->whole_dir_path);
            throw new DataNotFoundException([$this->translateService->getTranslation('SystemVolumen')]);
        }

        $file = $this->whole_dir_path . '/' . $this->query->getHashName() . $this->query->getPart();

        if (!$fsObject->exists($file)) {
            $this->addFileToFolder();
        }
    }

    private function checkOrCreateAudiobookFolder(Filesystem $fsObject): void
    {
        if (!$fsObject->exists($this->whole_dir_path)) {
            $old = umask(0);
            $fsObject->mkdir($this->whole_dir_path, 0775);
            umask($old);
        }
    }

    private function addFileToFolder(): void
    {
        $base64File = fopen($this->whole_dir_path . '/' . $this->query->getHashName() . $this->query->getPart(), 'wb');
        fwrite($base64File, $this->query->getBase64());
        fclose($base64File);
    }

    private function checkSystemStorage(string $dir): int
    {
        $size = 0;

        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : self::checkSystemStorage($each);
        }

        return $size;
    }

    public function lastFile(): bool
    {
        $this->checkConfiguration();

        $amountOfFiles = 0;

        if ($handle = opendir($this->whole_dir_path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..') {
                    ++$amountOfFiles;
                }
            }
            closedir($handle);
        }

        return ($amountOfFiles === $this->query->getParts());
    }

    public function combineFiles(): void
    {
        $this->checkConfiguration();

        $zipFile = fopen($this->whole_zip_path . '.zip', 'ab');

        $zipFiles = array_diff(scandir($this->whole_dir_path), ['.', '..']);
        $result = [];

        foreach ($zipFiles as $file) {
            $hash = strlen($this->query->getHashName());
            $result[] = substr($file, $hash);
        }

        sort($result);

        foreach ($result as $file) {
            $fileDir = $this->whole_dir_path . '/' . $this->query->getHashName() . $file;

            $partFile = fopen($fileDir, 'rb');

            $readData = fread($partFile, filesize($fileDir));

            fwrite($zipFile, base64_decode($readData, true));

            fclose($partFile);
        }

        fclose($zipFile);

        $this->removeFolder($this->whole_dir_path);
    }

    public function unzip(string $reAdding = null): string
    {
        $this->checkConfiguration();

        $file = $this->whole_zip_path . '.zip';

        $zip = new ZipArchive();

        $zip->open($file);

        $dir = trim($zip->getNameIndex(0), '/');
        $dir = explode("/", $dir)[0];

        $extracted = $zip->extractTo($_ENV['MAIN_DIR']);

        if (!$extracted) {
            $this->removeFolder($file);
        }

        $zip->close();

        unlink($file);

        if ($reAdding !== null && is_dir($reAdding)) {
            $this->removeFolder($reAdding);
        }

        $amountOfSameFolders = 0;

        if ($handle = opendir($_ENV['MAIN_DIR'])) {
            while (false !== ($entry = readdir($handle))) {
                if (str_contains($entry, $this->query->getFileName())) {
                    ++$amountOfSameFolders;
                }
            }
            closedir($handle);
        }

        $newName = $this->whole_zip_path . $amountOfSameFolders;

        rename($_ENV['MAIN_DIR'] . '/' . $dir, $newName);

        return $newName;
    }

    public function createAudiobookJsonData(string $folderDir): array
    {
        $this->checkConfiguration();

        $id3Data = [];
        $mp3Size = 0;
        $mp3Duration = 0;
        $parts = 0;

        if ($handle = opendir($folderDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..') {
                    $file_parts = pathinfo($entry);
                    if ($file_parts['extension'] === 'mp3') {
                        $mp3file = $entry;
                        if ($mp3file !== '') {
                            $parts++;

                            $mp3Dir = $folderDir . '/' . $mp3file;

                            $mp3Size += filesize($mp3Dir);

                            $this->audiobooksID3TagsReaderService->setFileName($mp3Dir);
                            $id3TrackData = $this->audiobooksID3TagsReaderService->getTagsInfo();

                            $mp3Duration += (int)$id3TrackData['playtime_seconds'];

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
                                    }

                                    $id3Data[$index] = $id3TrackData[$index];
                                }
                            }
                        }
                    } elseif ($file_parts['extension'] === 'jpg' || $file_parts['extension'] === 'jpeg' || $file_parts['extension'] === 'png') {
                        $img = $entry;
                        if ($img !== '') {
                            $imgDir = '/files/' . pathinfo($folderDir)['filename'] . '/' . $img;
                            $id3Data['imgFileDir'] = $imgDir;
                        }
                    }
                }
            }
            closedir($handle);
        }

        $id3Data['duration'] = $mp3Duration;
        $id3Data['size'] = number_format($mp3Size / 1048576, 2);
        $id3Data['parts'] = $parts;
        $id3Data['title'] = $this->query->getFileName();

        return $id3Data;
    }

    public function removeFolder(string $dir): bool
    {
        $it = null;

        try {
            $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        } catch (Throwable) {
        }

        if ($it) {
            $files = new RecursiveIteratorIterator(
                $it,
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
        }

        return !is_dir($dir) || rmdir($dir);
    }

    private function checkConfiguration(): void
    {
        if ($this->query === null) {
            throw new AudiobookConfigServiceException();
        }
    }
}
