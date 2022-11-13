<?php

namespace App\Service;


class MP3FileService implements MP3FileServiceInterface
{
    public string $fileName = "";

    public function configure(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getDuration(): string
    {
        $fd = fopen($this->fileName, "rb");

        $duration = 0;
        $block = fread($fd, 100);
        $offset = self::skipID3v2Tag($block);
        fseek($fd, $offset, SEEK_SET);
        while (!feof($fd)) {
            $block = fread($fd, 10);
            if (strlen($block) < 10) {
                break;
            } else if ($block[0] == "\xff" && (ord($block[1]) & 0xe0)) {
//                try{

                $info = self::parseFrameHeader(substr($block, 0, 4));

//                }catch(Exception $e){
//                    break;
//                }
                if (empty($info['Framesize'])) {
                    return $duration;
                }

                fseek($fd, $info['Framesize'] - 10, SEEK_CUR);
                $duration += ($info['Samples'] / $info['Sampling Rate']);

            } else if (str_starts_with($block, 'TAG')) {
                fseek($fd, 128 - 10, SEEK_CUR);
            } else {
                fseek($fd, -9, SEEK_CUR);
            }
        }
        return self::formatTime(round($duration));
    }

    /**
     * @param $duration
     * @return string
     */
    private static function formatTime($duration): string
    {
        $hours = floor($duration / 3600);
        $minutes = floor(($duration - ($hours * 3600)) / 60);
        $seconds = $duration - ($hours * 3600) - ($minutes * 60);

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }

    /**
     * @param $block
     * @return float|int
     */
    private function skipID3v2Tag(&$block): float|int
    {
        if (str_starts_with($block, "ID3")) {

            $id3v2_flags = ord($block[5]);

            $flag_footer_present = $id3v2_flags & 0x10 ? 1 : 0;

            $z0 = ord($block[6]);
            $z1 = ord($block[7]);
            $z2 = ord($block[8]);
            $z3 = ord($block[9]);

            if ((($z0 & 0x80) == 0) && (($z1 & 0x80) == 0) && (($z2 & 0x80) == 0) && (($z3 & 0x80) == 0)) {
                $header_size = 10;
                $tag_size = (($z0 & 0x7f) * 2097152) + (($z1 & 0x7f) * 16384) + (($z2 & 0x7f) * 128) + ($z3 & 0x7f);
                $footer_size = $flag_footer_present ? 10 : 0;
                return $header_size + $tag_size + $footer_size;
            }
        }
        return 0;
    }

    /**
     * @param $fourBytes
     * @return array
     */
    private static function parseFrameHeader($fourBytes): array
    {
        static $versions = array(
            0x0 => '2.5', 0x1 => 'x', 0x2 => '2', 0x3 => '1',
        );
        static $layers = array(
            0x0 => 'x', 0x1 => '3', 0x2 => '2', 0x3 => '1',
        );
        static $bitrates = array(
            'V1L1' => array(0, 32, 64, 96, 128, 160, 192, 224, 256, 288, 320, 352, 384, 416, 448),
            'V1L2' => array(0, 32, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320, 384),
            'V1L3' => array(0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320),
            'V2L1' => array(0, 32, 48, 56, 64, 80, 96, 112, 128, 144, 160, 176, 192, 224, 256),
            'V2L2' => array(0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160),
            'V2L3' => array(0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160),
        );
        static $sample_rates = array(
            '1' => array(44100, 48000, 32000),
            '2' => array(22050, 24000, 16000),
            '2.5' => array(11025, 12000, 8000),
        );
        static $samples = array(
            1 => array(1 => 384, 2 => 1152, 3 => 1152,),
            2 => array(1 => 384, 2 => 1152, 3 => 576,),
        );

        $b1 = ord($fourBytes[1]);
        $b2 = ord($fourBytes[2]);

        $version_bits = ($b1 & 0x18) >> 3;
        $version = $versions[$version_bits];
        $simple_version = ($version == '2.5' ? 2 : $version);

        $layer_bits = ($b1 & 0x06) >> 1;
        $layer = $layers[$layer_bits];

        $bitrate_key = sprintf('V%dL%d', $simple_version, $layer);
        $bitrate_idx = ($b2 & 0xf0) >> 4;
        $bitrate = $bitrates[$bitrate_key][$bitrate_idx] ?? 0;

        $sample_rate_idx = ($b2 & 0x0c) >> 2;
        $sample_rate = $sample_rates[$version][$sample_rate_idx] ?? 0;
        $padding_bit = ($b2 & 0x02) >> 1;


        $info = array();
        $info['Version'] = $version;
        $info['Layer'] = $layer;
        $info['Bitrate'] = $bitrate;
        $info['Sampling Rate'] = $sample_rate;
        $info['FrameSize'] = self::frameSize($layer, $bitrate, $sample_rate, $padding_bit);

        $info['Samples'] = $samples[$simple_version][$layer];
        return $info;
    }

    /**
     * @param $layer
     * @param $bitrate
     * @param $sample_rate
     * @param $padding_bit
     * @return int
     */
    private static function frameSize($layer, $bitrate, $sample_rate, $padding_bit): int
    {
        if ($layer == 1)
            return intval(((12 * $bitrate * 1000 / $sample_rate) + $padding_bit) * 4);
        else
            return intval(((144 * $bitrate * 1000) / $sample_rate) + $padding_bit);
    }
}