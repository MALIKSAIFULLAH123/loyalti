<?php

namespace MetaFox\Storage\Support;

use Illuminate\Contracts\Filesystem\Cloud as CloudFilesystemContract;
use Illuminate\Contracts\Filesystem\Filesystem;

class Task implements CloudFilesystemContract
{
    private mixed $storage;

    private mixed $configId;

    private Filesystem $disk;

    /**
     * @param mixed      $storage
     * @param mixed      $configId
     * @param Filesystem $disk
     */
    public function __construct(mixed $storage, mixed $configId, Filesystem $disk)
    {
        $this->storage = $storage;
        $this->configId = $this->configId;
        $this->disk = $disk;
    }

    public function exists($path)
    {
        return $this->disk->exists($path);
    }

    public function get($path)
    {
        return $this->disk->get($path);
    }

    public function readStream($path)
    {
        return $this->disk->readStream($path);
    }

    public function put($path, $contents, $options = [])
    {
        return $this->disk->put($path, $contents, $options);
    }

    public function writeStream($path, $resource, array $options = [])
    {
        return $this->disk->writeStream($path, $resource, $options);
    }

    public function getVisibility($path)
    {
        return $this->disk->getVisibility($path);
    }

    public function setVisibility($path, $visibility)
    {
        return $this->disk->setVisibility($path, $visibility);
    }

    public function prepend($path, $data)
    {
        return $this->disk->prepend($path, $data);
    }

    public function append($path, $data)
    {
        return $this->disk->append($path, $data);
    }

    public function delete($paths)
    {
        return $this->disk->delete($paths);
    }

    public function copy($from, $to)
    {
        return $this->disk->copy($from, $to);
    }

    public function move($from, $to)
    {
        return $this->disk->move($from, $to);
    }

    public function size($path)
    {
        return $this->disk->size($path);
    }

    public function lastModified($path)
    {
        return $this->disk->lastModified($path);
    }

    public function files($directory = null, $recursive = false)
    {
        return $this->disk->files($directory, $recursive);
    }

    public function allFiles($directory = null)
    {
        return $this->disk->allFiles($directory);
    }

    public function directories($directory = null, $recursive = false)
    {
        return $this->disk->directories($directory);
    }

    public function allDirectories($directory = null)
    {
        return $this->disk->allDirectories($directory);
    }

    public function makeDirectory($path)
    {
        return $this->disk->makeDirectory($path);
    }

    public function deleteDirectory($directory)
    {
        return $this->disk->deleteDirectory($directory);
    }

    public function url($path)
    {
        return $this->disk->url($path);
    }

    public function download($path)
    {
        return $this->disk->download($path);
    }
}
