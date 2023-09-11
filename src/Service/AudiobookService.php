<?php

namespace App\Service;


use App\Enums\AudiobookArchiveType;
use App\Exception\AudiobookConfigServiceException;
use App\Exception\DataNotFoundException;
use App\Query\AdminAudiobookAddQuery;
use App\Query\AdminAudiobookReAddingQuery;
use FilesystemIterator;
use RarArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class AudiobookService implements AudiobookServiceInterface
{
    private AudiobooksID3TagsReaderService $audiobooksID3TagsReaderService;
    private AdminAudiobookAddQuery|AdminAudiobookReAddingQuery|null $query = null;
    private string $whole_dir_path = "";
    private string $whole_zip_path = "";
    private AudiobookArchiveType|null $archiveType = null;
    private TranslateService $translateService;

    /**
     * @param AudiobooksID3TagsReaderService $audiobooksID3TagsReaderService
     * @param TranslateService $translateService
     */
    public function __construct(AudiobooksID3TagsReaderService $audiobooksID3TagsReaderService, TranslateService $translateService)
    {
        $this->audiobooksID3TagsReaderService = $audiobooksID3TagsReaderService;
        $this->translateService = $translateService;
    }

    public function configure(AdminAudiobookAddQuery|AdminAudiobookReAddingQuery $query): void
    {
        $this->query = $query;
        $this->whole_dir_path = $_ENV['MAIN_DIR'] . "/" . $this->query->getHashName();
        $this->whole_zip_path = $_ENV['MAIN_DIR'] . "/" . $this->query->getFileName();
        $this->archiveType = $this->query->getArchiveType();
    }

    /**
     * @return void
     * @throws AudiobookConfigServiceException|DataNotFoundException
     */
    public function checkAndAddFile(): void
    {
        self::checkConfiguration();

        $fsObject = new Filesystem();

        $size = self::checkSystemStorage($_ENV['MAIN_DIR']);

        self::checkOrCreateAudiobookFolder($fsObject);

        if ($size >= $_ENV['INSTITUTION_VOLUMEN']) {
            self::removeFolder($this->whole_dir_path);
            throw new DataNotFoundException([$this->translateService->getTranslation("SystemVolumen")]);
        }

        $file = $this->whole_dir_path . "/" . $this->query->getHashName() . $this->query->getPart();

        if (!$fsObject->exists($file)) {
            self::addFileToFolder();
        }
    }

    /**
     * @param Filesystem $fsObject
     * @return void
     */
    private function checkOrCreateAudiobookFolder(Filesystem $fsObject): void
    {
        if (!$fsObject->exists($this->whole_dir_path)) {
            $old = umask(0);
            $fsObject->mkdir($this->whole_dir_path, 0775);
            umask($old);
        }
    }

    /**
     * @return void
     */
    private function addFileToFolder(): void
    {
        $base64File = fopen($this->whole_dir_path . "/" . $this->query->getHashName() . $this->query->getPart(), "w");
        fwrite($base64File, $this->query->getBase64());
        fclose($base64File);
    }

    /**
     * @throws DataNotFoundException
     */
    private function checkSystemStorage(string $dir): int
    {
        $size = 0;

        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : self::checkSystemStorage($each);
        }

        return $size;
    }

    /**
     * @return bool
     * @throws AudiobookConfigServiceException
     */
    public function lastFile(): bool
    {
        self::checkConfiguration();

        $amountOfFiles = 0;

        if ($handle = opendir($this->whole_dir_path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $amountOfFiles = $amountOfFiles + 1;
                }
            }
            closedir($handle);
        }

        return ($amountOfFiles == $this->query->getParts());
    }

    /**
     * @throws AudiobookConfigServiceException
     */
    public function combineFiles(): void
    {
        self::checkConfiguration();

        $zipFile = fopen($this->whole_zip_path . "." . $this->archiveType->value, "a");

        $zipFiles = array_diff(scandir($this->whole_dir_path), array('.', '..'));
        $result = [];

        foreach ($zipFiles as $file) {
            $hash = strlen($this->query->getHashName());
            $result[] = substr($file, $hash);
        }

        sort($result);

        foreach ($result as $file) {

            $fileDir = $this->whole_dir_path . "/" . $this->query->getHashName() . $file;

            $partFile = fopen($fileDir, "r");

            $readData = fread($partFile, filesize($fileDir));

            fwrite($zipFile, base64_decode($readData, true));

            fclose($partFile);
        }

        fclose($zipFile);

        self::removeFolder($this->whole_dir_path);
    }

    /**
     * @param string $file
     * @return string
     */
    public function unzipRAR(string $file): string
    {
        $rar = RarArchive::open($file);

//        if ($rar === false) {
//            throw new Exception("Failed to open RAR archive");
//        }

        $entries = $rar->getEntries();

//        if (empty($entries)) {
//            throw new Exception("No entries found in the RAR archive");
//        }

        $firstEntry = $entries[0];

        $dir = rtrim($firstEntry->getName(), '/');

        $extracted = $firstEntry->extract($_ENV['MAIN_DIR']);

        if (!$extracted) {
            self::removeFolder($file);
        }

        $rar->close();

        return $dir;
    }
    
    /**
     * @param string $file
     * @return string
     */
    public function unzipWINRAR(string $file): string
    {
        $zip = new ZipArchive;

        $zip->open($file);

        $dir = trim($zip->getNameIndex(0), '/');

        $extracted = $zip->extractTo($_ENV['MAIN_DIR']);

        if (!$extracted) {
            self::removeFolder($file);
        }

        $zip->close();

        return $dir;
    }

    /**
     * @param string|null $reAdding
     * @return string
     * @throws AudiobookConfigServiceException
     */
    public function unzip(string $reAdding = null): string
    {
        self::checkConfiguration();

        $file = $this->whole_zip_path . "." . $this->archiveType->value;

        $dir = match ($this->archiveType) {
            AudiobookArchiveType::ZIP => self::unzipWINRAR($file),
            AudiobookArchiveType::RAR => self::unzipRAR($file),
        };

        unlink($file);

        if ($reAdding != null && is_dir($reAdding)) {
            self::removeFolder($reAdding);
        }

        $amountOfSameFolders = 0;

        if ($handle = opendir($_ENV['MAIN_DIR'])) {
            while (false !== ($entry = readdir($handle))) {

                if (str_contains($entry, $this->query->getFileName())) {
                    $amountOfSameFolders = $amountOfSameFolders + 1;
                }
            }
            closedir($handle);
        }

        $newName = $this->whole_zip_path . $amountOfSameFolders;

        rename($_ENV['MAIN_DIR'] . "/" . $dir, $newName);

        return $newName;
    }

    /**
     * @param string $folderDir
     * @return array
     * @throws AudiobookConfigServiceException
     */
    public function createAudiobookJsonData(string $folderDir): array
    {
        self::checkConfiguration();

        $id3Data = [];
        $mp3Size = 0;
        $mp3Duration = 0;
        $parts = 0;

        if ($handle = opendir($folderDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $file_parts = pathinfo($entry);
                    if ($file_parts['extension'] == "mp3") {
                        $mp3file = $entry;
                        if ($mp3file !== "") {

                            $parts++;

                            $mp3Dir = $folderDir . "/" . $mp3file;

                            $mp3Size = $mp3Size + filesize($mp3Dir);

                            $this->audiobooksID3TagsReaderService->setFileName($mp3Dir);
                            $id3TrackData = $this->audiobooksID3TagsReaderService->getTagsInfo();

                            $mp3Duration = $mp3Duration + intval($id3TrackData["playtime_seconds"]);

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
        $id3Data['title'] = $this->query->getFileName();

        return $id3Data;
    }

    /**
     * @param string $dir
     * @return bool
     */
    public function removeFolder(string $dir): bool
    {
        $it = null;

        try {
            $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        } catch (\UnexpectedValueException) {
        }

        if ($it) {
            $files = new RecursiveIteratorIterator($it,
                RecursiveIteratorIterator::CHILD_FIRST);

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

    /**
     * @return void
     * @throws AudiobookConfigServiceException
     */
    private function checkConfiguration(): void
    {
        if ($this->query == null) {
            throw new AudiobookConfigServiceException();
        }
    }
}