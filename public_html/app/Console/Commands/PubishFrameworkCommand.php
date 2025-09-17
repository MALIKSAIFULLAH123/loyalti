<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MetaFox\App\Support\MetaFoxStore;
use MetaFox\Platform\MetaFox;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Input\InputOption;
use ZipArchive;

class PubishFrameworkCommand extends Command
{
    protected $name = 'publish:framework';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bundle a package and publish to store';
    private array $excludePaths = [];
    private array $includePaths = [];

    public function handle()
    {
        $packages = config('metafox.packages');
        $channel = $this->option('production')? 'production': 'development';

        $tmp = tempnam(sys_get_temp_dir(), 'metafox-framework') . '.zip';
        if (file_exists($tmp)) {
            unlink($tmp);
        }

        $zipArchive = new ZipArchive();
        $zipArchive->open($tmp, ZipArchive::CREATE);

        $checkSums = [];
        $this->includePaths = [
            base_path('app'),
            base_path('bootstrap'),
            base_path('config'),
            base_path('database'),
            base_path('lang'),
            base_path('resources'),
            base_path('resources'),
            base_path('resources'),
            base_path('resources'),
            base_path('composer.json'),
            base_path('composer.lock'),
            base_path('public'),
            base_path('storage/app/web'),
            base_path('public/install'),
        ];

        $this->excludePaths = [
            base_path('public/web'),
            base_path('public/storage'),
        ];

        foreach ($packages as $name => $package) {
            if (!$package['core'] ?? false) {
                continue;
            }
            $this->includePaths[] = base_path($package['path']);
        }

        foreach ($this->includePaths as $path) {
            $this->addFiles($zipArchive, $path, $checkSums);
        }

        $this->info(str_pad('', 60, '='));

        $zipArchive->addFromString('checksum.lock', implode(PHP_EOL, $checkSums));

        $numFiles = $zipArchive->numFiles;
        $zipArchive->close();
        $fileSize = number_format(filesize($tmp) / 1024 / 1024, 2);

        $this->info(sprintf('Compressed %s %d files, %s MB', $tmp, $numFiles, $fileSize));

        $name = 'metafox-framework-' . MetaFox::getVersion() . '-'. $channel. '.zip';
        app(MetaFoxStore::class)
            ->publishToStore('metafox/framework', MetaFox::getVersion(), $name, $tmp, $channel);

        return 0;
    }

    public function addFiles(ZipArchive $zipArchive, string $path, array &$checkSums)
    {
        if (!file_exists($path)) {
            return;
        }

        if (is_file($path)) {
            $this->addFile($zipArchive, new \SplFileInfo($path), $checkSums);

            return;
        }

        $directory = new RecursiveDirectoryIterator($path, \FilesystemIterator::FOLLOW_SYMLINKS);
        /** @var \SplFileInfo[] $iterator */
        $iterator = new RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            if ($file->isDir() || !$file->isReadable() || $file->isLink() || !$this->filter($file)) {
                continue;
            }
            $this->addFile($zipArchive, $file, $checkSums);
        }
    }

    public function addFile(ZipArchive $zipArchive, \SplFileInfo $file, array &$checkSums)
    {
        $name = substr($file->getPathname(), strlen(base_path()) + 1);
        $this->info("Add file: " . $name);
        $zipArchive->addFile($file->getPathname(), $name);

        $checkSums[] = sprintf('%s %s', hash_file('md5', $file->getPathname()), $name);
    }

    public function filter(\SplFileInfo $file): bool
    {
        if (in_array($file->getExtension(), ['log', 'DS_Store', 'zip', 'rar'])) {
            return false;
        }

        $path = $file->getPathname();

        foreach ($this->excludePaths as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return false;
            }
        }

        return true;
    }

    protected function getOptions(): array
    {
        return [
            ['production', null, InputOption::VALUE_NONE, 'Release channel on production'],
        ];
    }
}
