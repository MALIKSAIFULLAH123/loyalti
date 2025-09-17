<?php

namespace MetaFox\Storage\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;

class AliasFilesystemFactory extends FilesystemManager
{
    public function __construct()
    {
        parent::__construct(app());
    }

    public function make(FilesystemManager $manager, string $target, array $config): Filesystem
    {
        $repository = $this->app['config'];
        $key        = "filesystems.disks.{$target}";

        $backupConfig = $repository->get($key, []);

        $repository->set($key, array_merge($backupConfig, $config));

        $fileSystem = $manager->resolve($target);

        $repository->set($key, $backupConfig);

        return $fileSystem;
    }
}
