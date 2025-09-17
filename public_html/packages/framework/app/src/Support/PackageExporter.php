<?php

namespace MetaFox\App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\PackageManager;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use ZipArchive;

/**
 * Class Export.
 */
class PackageExporter
{
    /**
     * @param  string  $name
     * @param  string  $version
     * @param  bool  $local
     * @return string
     */
    public function getExportPath(string $name, string $version, bool $local): string
    {
        if ($local) {
            $suffix = date('ymd');

            return sprintf(
                '%s/%s-%s-%s.zip',
                'exports',
                str_replace('/', '-', $name),
                $version,
                $suffix
            );
        }

        return sprintf(
            '%s-%s.zip',
            str_replace('/', '-', $name),
            $version
        );
    }

    /**
     * Execute the console command.
     *
     * @param  string  $package
     * @param  bool|null  $release
     * @param  string  $channel
     * @param  ZipArchive|null  $archive
     * @param  bool|null  $continue
     * @return string|null
     */
    public function export(
        string $package,
        bool $release,
        string $channel,
        ?ZipArchive $archive = null,
        ?bool $continue = false
    ): ?string {
        $json = PackageManager::getComposerJson($package);

        if(!$json){
            throw new \InvalidArgumentException("Failed export package $package");
        }

        $version = Arr::get($json, 'version');
        $require = Arr::get($json, 'extra.metafox.require', []);
        $dir = Arr::get($json, 'extra.metafox.path');
        $frontendPaths = Arr::get($json, 'extra.metafox.frontendPaths');
        $peerDependencies = Arr::get($json, 'extra.metafox.peerDependencies');
        $frontendRoot = config('app.mfox_frontend_root');
        $checksumFile = sprintf("%s/%s/checksum",MetaFoxConstant::BACKEND_WRAP_NAME,Arr::get($json, 'extra.metafox.path'));
        $tmp = null;
        $root = base_path();

        if (!$archive) {
            $archive = new ZipArchive();
            $tmp = tempnam(sys_get_temp_dir(), 'bundle'); // good
            if (file_exists($tmp)) {
                @unlink($tmp);
            }
            if (!$archive->open($tmp, ZipArchive::CREATE)) {
                throw new InvalidArgumentException(sprintf('Could not create archive at %s', $tmp));
            }
        }

        if (!$dir) {
            throw new \InvalidArgumentException('Missing path for '.$package);
        }

        $dir = base_path($dir);
        $checksumContent = [];
        $this->addDirectory($archive, $dir, $root, MetaFoxConstant::BACKEND_WRAP_NAME,$checksumContent);

        if ($frontendRoot && !is_dir($frontendRoot)) {
            throw new \RuntimeException('Failed finding '.$frontendRoot);
        }

        if ($frontendRoot && is_array($frontendPaths)) {
            $this->log('Checking ', $frontendPaths);
            foreach ($frontendPaths as $frontendPath) {
                $frontendDir = realpath($frontendRoot.'/'.$frontendPath);
                if (!is_dir($frontendDir)) {
                    throw new \RuntimeException('Failed getting '.$frontendRoot.'/'.$frontendPath);
                }
                $this->addDirectory($archive, $frontendDir, $frontendRoot, MetaFoxConstant::FRONTEND_WRAP_NAME,$checksumContent);
            }
        }

        if (!$archive->getFromName(MetaFoxConstant::FRONTEND_WRAP_NAME)) {
            // add frontend directory
            $archive->addEmptyDir(MetaFoxConstant::FRONTEND_WRAP_NAME);
        }

        if ($peerDependencies) {
            foreach ($peerDependencies as $dependency) {
                if(!PackageManager::getComposerJson($dependency)){
                    throw new \InvalidArgumentException("Failed export peer dependency package [$dependency]");
                }
                $this->export($dependency, false, $channel, $archive);
            }
        }

        // do not add check sum if is child bundle.
        if ($continue || !$tmp) {
            return null;
        }

        $archive->addFromString($checksumFile, implode(PHP_EOL, $checksumContent));
        $numFiles = $archive->numFiles;
        $checksum = $this->calculatePackageChecksum($archive);

        $archive->close();
        $name = str_replace('/', '-', $package).'-'.$version.'.zip';

        $this->log(sprintf('bundled %s %d -> %s', $tmp, $numFiles, $name));
        $this->log(sprintf('checksum %s', $checksum));

        // test order checksum.
        if ($release) {
            app(MetaFoxStore::class)->publishToStore($package, $version, $name, $tmp, $channel, $require);
            $this->log(sprintf('Uploaded %s to MetaFox store', $name));
        }

        return $tmp;
    }

    public function calculatePackageChecksum(ZipArchive $archive): string
    {
        $sum = [];

        for ($index = 0; $index < $archive->numFiles; $index++) {
            if (($content = $archive->getFromIndex($index))) {
                $sum[] = sha1($content);
            }
        }
        $checksum = sha1(implode(PHP_EOL, $sum));

        return $checksum;
    }

    private function addDirectory(ZipArchive $archive, string $dir, string $root, string $prefix, array &$checksum): void
    {
        if (app('files')->exists($dir)) {
            $this->log(sprintf('Adding path %s', $dir));
        } else {
            $this->log(sprintf('Path not found "%s"', $dir));

            return;
        }
        $flags = \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::SKIP_DOTS | \FileSystemIterator::CURRENT_AS_FILEINFO;
        /** @var SplFileInfo[] $rii */
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,$flags));
        $addToChecksum = $prefix  == MetaFoxConstant::BACKEND_WRAP_NAME;

        foreach ($rii as $file) {
            if ($file->isDir()) {
                continue;
            } elseif ($file->isLink()) {
                continue;
            } else {
                $from = $file->getPathname();
                $named = substr($from, strlen($root) + 1);
                $to = sprintf('%s/%s', $prefix, $named);
                $this->log('Added file '.$to);
                $result = $archive->addFile($from, $to);
                if($addToChecksum){
                    $checksum[] = sprintf("%s %s", hash_file('md5', $from, false), $named);
                }
                if (!$result) {
                    $this->log(sprintf('Could not archive %s', $from));
                }
            }
        }
    }

    public function log(string $text, array $context = []): void
    {
        Log::channel('dev')->info($text, $context);
    }
}
