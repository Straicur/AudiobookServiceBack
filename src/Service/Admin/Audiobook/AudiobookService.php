<?php

declare(strict_types = 1);

namespace App\Service\Admin\Audiobook;

use App\Exception\AudiobookConfigServiceException;
use App\Exception\DataNotFoundException;
use App\Query\Admin\AdminAudiobookAddFileInterface;
use App\Service\TranslateServiceInterface;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;
use ZipArchive;

use function array_key_exists;
use function in_array;
use function strlen;

use const GLOB_NOSORT;

class AudiobookService implements AudiobookServiceInterface
{
    private ?AdminAudiobookAddFileInterface $query = null;

    private string $whole_dir_path = '';

    private string $whole_zip_path = '';

    public function __construct(
        private readonly AudiobooksID3TagsReaderServiceInterface $audiobooksID3TagsReaderService,
        private readonly TranslateServiceInterface $translateService,
    ) {}

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

        if ((int) $_ENV['INSTITUTION_VOLUMEN'] <= $size) {
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
            $fsObject->mkdir($this->whole_dir_path, 0o775);
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
                if ('.' !== $entry && '..' !== $entry) {
                    ++$amountOfFiles;
                }
            }

            closedir($handle);
        }

        return $this->query->getParts() === $amountOfFiles;
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

    public function unzip(?string $reAdding = null): string
    {
        $this->checkConfiguration();

        $file = $this->whole_zip_path . '.zip';

        $zip = new ZipArchive();

        $zip->open($file);

        $dir = trim($zip->getNameIndex(0), '/');
        $dir = explode('/', $dir)[0];

        $extracted = $zip->extractTo($_ENV['MAIN_DIR']);

        if (!$extracted) {
            $this->removeFolder($file);
        }

        $zip->close();

        unlink($file);

        if (null !== $reAdding && is_dir($reAdding)) {
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
        $imgDir = null;

        if ($handle = opendir($folderDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ('.' !== $entry && '..' !== $entry) {
                    $file_parts = pathinfo($entry);
                    if ('mp3' === $file_parts['extension'] && !empty($entry)) {
                        $mp3file = $entry;
                        ++$parts;

                        $mp3Dir = $folderDir . '/' . $mp3file;

                        $mp3Size += filesize($mp3Dir);

                        $this->audiobooksID3TagsReaderService->setFileName($mp3Dir);
                        $id3TrackData = $this->audiobooksID3TagsReaderService->getTagsInfo();

                        $mp3Duration += (int) $id3TrackData['playtime_seconds'];

                        if (array_key_exists('tags', $id3TrackData) && !empty($id3TrackData['tags'])) {
                            $id3Tags = current($id3TrackData['tags']);

                            $sameKeys = array_intersect_key($id3Data, $id3Tags);

                            $keys = array_keys($id3Tags);

                            foreach ($keys as $key => $index) {
                                if (array_key_exists($key, $sameKeys)) {
                                    continue;
                                }

                                $id3Data[$index] = current($id3Tags[$index]);
                            }
                        }
                    } elseif (in_array($file_parts['extension'], ['jpg', 'jpeg', 'png'], true)) {
                        $img = $entry;
                        if (null === $imgDir) {
                            $imgDir = '/files/' . pathinfo($folderDir)['filename'] . '/' . $img;
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

        if (null !== $imgDir) {
            $id3Data['imgFileDir'] = $imgDir;
        }

        return $id3Data;
    }

    public function removeFolder(string $dir): bool
    {
        $it = null;

        try {
            $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        } catch (Throwable) {
        }

        if ($it instanceof RecursiveDirectoryIterator) {
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
        if (null === $this->query) {
            throw new AudiobookConfigServiceException();
        }
    }
}
