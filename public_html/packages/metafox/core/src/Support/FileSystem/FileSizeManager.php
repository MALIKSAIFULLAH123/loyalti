<?php

namespace MetaFox\Core\Support\FileSystem;

class FileSizeManager
{
    public static function convertFileSizeToText(int $size, int $precision = 2): string
    {
        $tbSize = 1099511627776;
        $gbSize = 1073741824;
        $mbSize = 1048576;
        $kbSize = 1024;

        if ($size >= $tbSize) {
            $fSize = $size / $tbSize;
            $unit = 'TB';

            return round($fSize, $precision) . ' ' . $unit;
        }

        if ($size >= $gbSize) {
            $fSize = $size / $gbSize;
            $unit = 'GB';

            return round($fSize, $precision) . ' ' . $unit;
        }

        if ($size >= $mbSize) {
            $fSize = $size / $mbSize;
            $unit = 'MB';

            return round($fSize, $precision) . ' ' . $unit;
        }

        if ($size >= $kbSize) {
            $fSize = $size / $kbSize;
            $unit = 'KB';

            return round($fSize, $precision) . ' ' . $unit;
        }

        return round($size, $precision) . ' ' . 'B';
    }
}
