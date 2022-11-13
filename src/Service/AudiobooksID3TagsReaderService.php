<?php

namespace App\Service;

class AudiobooksID3TagsReaderService implements AudiobooksID3TagsReaderServiceInterface
{
    private array $systemTags = array(
        'TIT2',
        'TALB',
        'TPE1',
        'TPE2',
        'TRCK',
        'TYER',
        'TLEN',
        'USLT',
        'TPOS',
        'TCON',
        'TENC',
        'TCOP',
        'TPUB',
        'TOPE',
        'WXXX',
        'COMM',
        'TCOM'
    );
    private array $systemTagsTitles = array(
        'title',
        'album',
        'author',
        'album_author',
        'track',
        'year',
        'length',
        'lyric',
        'desc',
        'genre',
        'encoded',
        'copyright',
        'publisher',
        'original_artist',
        'url',
        'comments',
        'composer'
    );
    private array $olderSystemTags = array(
        'TT2',
        'TAL',
        'TP1',
        'TRK',
        'TYE',
        'TLE',
        'ULT'
    );
    private array $olderSystemTagsTitles = array(
        'title',
        'album',
        'author',
        'track',
        'year',
        'length',
        'lyric'
    );

    public function getTagsInfo(string $path): array
    {
        $fileSize = filesize($path);

        $file = fopen($path, 'r');

        $fileData = fread($file, $fileSize);

        fclose($file);

        if (str_starts_with($fileData, 'ID3')) {

            $resultInfo['filename'] = $path;
            $resultInfo['version'] = hexdec(bin2hex(substr($fileData, 3, 1))) . '.' . hexdec(bin2hex(substr($fileData, 4, 1)));

        }
        if ($resultInfo['version'] == '4.0' || $resultInfo['version'] == '3.0') {
            for ($i = 0; $i < count($this->systemTags); $i++) {
                if (strpos($fileData, $this->systemTags[$i] . chr(0))) {

                    $pom = '';
                    $iPosition = strpos($fileData, $this->systemTags[$i] . chr(0));
                    $iLength = hexdec(bin2hex(substr($fileData, ($iPosition + 5), 3)));
                    $data = substr($fileData, $iPosition, 9 + $iLength);

                    for ($j = 0; $j < strlen($data); $j++) {
                        $char = substr($data, $j, 1);
                        if ($char >= ' ' && $char <= '~')
                            $pom .= $char;
                    }

                    if (substr($pom, 0, 4) == $this->systemTags[$i]) {

                        $iSpecificationLength = 4;

                        if ($this->systemTags[$i] == 'USLT') {
                            $iSpecificationLength = 7;
                        } elseif ($this->systemTags[$i] == 'TALB') {
                            $iSpecificationLength = 5;
                        } elseif ($this->systemTags[$i] == 'TENC') {
                            $iSpecificationLength = 6;
                        }

                        $resultInfo[$this->systemTagsTitles[$i]] = substr($pom, $iSpecificationLength);
                    }
                }
            }
        }

        if ($resultInfo['version'] == '2.0') {
            for ($i = 0; $i < count($this->olderSystemTags); $i++) {
                if (strpos($fileData, $this->olderSystemTags[$i] . chr(0))) {

                    $pom = '';
                    $iPosition = strpos($fileData, $this->olderSystemTags[$i] . chr(0));
                    $iLength = hexdec(bin2hex(substr($fileData, ($iPosition + 3), 3)));
                    $data = substr($fileData, $iPosition, 6 + $iLength);

                    for ($j = 0; $j < strlen($data); $j++) {

                        $char = substr($data, $j, 1);

                        if ($char >= ' ' && $char <= '~') {
                            $pom .= $char;
                        }
                    }

                    if (substr($pom, 0, 3) == $this->olderSystemTags[$i]) {

                        $iSpecificationLength = 3;

                        if ($this->olderSystemTags[$i] == 'ULT') {
                            $iSpecificationLength = 6;
                        }

                        $resultInfo[$this->olderSystemTagsTitles[$i]] = substr($pom, $iSpecificationLength);
                    }
                }
            }
        }
        return $resultInfo;
    }
}