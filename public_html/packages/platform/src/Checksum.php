<?php

namespace MetaFox\Platform;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Checksum
{

    private static function getFilesIterator(string $fullPath, string $base_path, bool $with_packages)
    {
        $exclude_files = [
            'bootstrap/cache/events.php',
            'bootstrap/cache/config.php',
            'bootstrap/cache/routes-v7.php',
            'bootstrap/cache/services.php',
            'config/metafox.php'
        ];
        $exclude_dirs = array_filter([
            $base_path.'vendor',
            $base_path.'.github',
            $base_path.'.idea',
            $base_path.'.vscode',
            $base_path.'.git',
            $base_path.'.phpunit',
            $base_path.'.well-known',
            $base_path.'bootstrap/cache',
            $base_path.'public/install',
            $base_path.'storage/framework',
            $base_path.'storage/composer',
            $base_path.'storage/app/web',
            $base_path.'storage/app/public',
            $with_packages ? false : $base_path.'packages',
        ], fn($x) => !!$x);

        $flags = FilesystemIterator::UNIX_PATHS | FilesystemIterator::SKIP_DOTS | FileSystemIterator::CURRENT_AS_FILEINFO;
        $dir = new RecursiveDirectoryIterator($fullPath, $flags);
        $files = new \RecursiveCallbackFilterIterator($dir, function ($file) use (&$exclude_files, &$exclude_dirs) {
            $name = $file->getRealpath();
            if ("checksum" == $file->getBasename()) {
                return false;
            }
            if (!$file->isReadable()) {
                return false;
            }
            if ($file->isDir()) {
                return !in_array($name, $exclude_dirs);
            }
            if (in_array($name, $exclude_files)) {
                return false;
            }
            return true;
        });
        /** @var \SplFileInfo[] $iterator */
        return new RecursiveIteratorIterator($files);
    }

    private static function _generateChecksum(array $target, string $base_path, bool $with_packages): array
    {
        $checksum = [];
        $base_len = strlen($base_path);
        $check = function (string $realPath) use ($base_len, &$checksum, &$exclude_files) {
            $name = substr($realPath, $base_len);
            if (!is_readable($realPath) || !is_file($realPath)) {
                return;
            }
            $checksum[$name] = hash_file('md5', $realPath, false);
        };

        foreach ($target as $p) {
            $fullPath = $base_path.$p;
            if (is_file($fullPath)) {
                $check($fullPath);
            }
            if (is_dir($fullPath)) {
                /** @var \SplFileInfo[] $iterator */
                $iterator = static::getFilesIterator($fullPath, $base_path, $with_packages);
                foreach ($iterator as $file) {
                    $check($file->getRealPath());
                }
            }
        }
        return $checksum;
    }

    public static function generateChecksum(
        array $target,
        string $save_to,
        string $base_path,
        bool $with_packages
    ): string {
        $checksum = static::_generateChecksum($target, $base_path, $with_packages);
        $content = [];
        foreach ($checksum as $name => $value) {
            $content[] = sprintf("%s %s", $value, $name);
        }

        file_put_contents($base_path.$save_to, implode(PHP_EOL, $content));

        return $save_to;
    }


    public static function parseChecksum(array $from_files): array
    {
        $from_array = [];
        $read_checksum = function (string $file) use (&$from_array) {
            foreach (explode(PHP_EOL, file_get_contents($file)) as $line) {
                $arr = explode(" ", $line, 2);
                if (count($arr) == 2) {
                    $from_array[$arr[1]] = $arr[0];
                }
            }
        };
        foreach ($from_files as $file) {
            $fullPath = base_path($file);
            if (!file_exists($fullPath)) {
                continue;
            }
            $read_checksum($fullPath);
        }

        return $from_array;
    }

    public static function listChecksumFiles(): array
    {
        $files = ["checksum", "packages/platform/checksum"];
        $base_len = strlen(base_path("/"));
        $dirs = ["packages/**/*/checksum", "packages/*/checksum"];
        foreach ($dirs as $dir) {
            foreach (glob(base_path($dir)) as $file) {
                $files[] = substr($file, $base_len);
            }
        }
        return $files;
    }

    public static function generatePlatformChecksum(string $base_path): array
    {
        $files = [];
        $files[] = static::generateChecksum(["", "packages/.htaccess"], "checksum", $base_path, false);

        foreach (config('metafox.packages') as $p) {
            if (@$p['path']) {
                $path = $p['path'];
                if (!is_dir($base_path.$path)) {
                    continue;
                }
                $files[] = static::generateChecksum(["$path"], $path.'/checksum', $base_path, false);
            }
        }
        return $files;
    }

    public static function testChecksum(): array
    {
        $checksumFiles = static::listChecksumFiles();
        $from_array = static::parseChecksum($checksumFiles);
        $to_array = static::_generateChecksum([""], base_path("/"), true);
        $diff = array_merge(array_diff_assoc($from_array, $to_array), array_diff_assoc($to_array, $from_array));
        $result = [];

        foreach ($diff as $name => $md5) {
            $status = null;
            if(str_ends_with($name, "/checksum")){
                // skip
            }elseif (array_key_exists($name, $from_array) && array_key_exists($name, $to_array)) {
                $status = "modified";
            } elseif (array_key_exists($name, $from_array)) {
                $status = "deleted";
            } elseif (array_key_exists($name, $to_array)) {
                $status = "created";
            }
            if ($status) {
                $result[] = [
                    'name'   => $name,
                    'status' => $status,
                ];
            }
        }
        return $result;
    }
}