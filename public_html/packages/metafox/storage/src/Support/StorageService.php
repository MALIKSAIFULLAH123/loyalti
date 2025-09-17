<?php

namespace MetaFox\Storage\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\RequestLifecycle;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Storage\Repositories\DiskRepositoryInterface;
use MetaFox\Storage\Repositories\FileRepositoryInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mime\MimeTypes;

class StorageService
{
    private FileRepositoryInterface $fileRepository;
    private DiskRepositoryInterface $diskRepository;

    /**
     * @param FileRepositoryInterface $fileRepository
     * @param DiskRepositoryInterface $diskRepository
     */
    public function __construct(FileRepositoryInterface $fileRepository, DiskRepositoryInterface $diskRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->diskRepository = $diskRepository;
    }

    /**
     * get list of urls of all variants.
     * @param  int        $id
     * @return array|null
     */
    public function getUrls(mixed $id): ?array
    {
        if (!$id) {
            return null;
        }

        $result = [];

        foreach ($this->getByOriginals($id) as $file) {
            $result["$file->variant"] = $file->url;
        }

        return $result;
    }

    public function getUrl(mixed $id): ?string
    {
        if (!$id) {
            return null;
        }

        try {
            $file = LoadReduce::remember(
                sprintf('files::fileById(%s)', $id),
                fn() => $this->fileRepository->find($id)
            );
        } catch (\Throwable) {
            // silent
            return null;
        }

        return $file?->url;
    }

    /**
     * steps:
     * 1. check clone state.
     * 2. remove external
     * 3. delete storage_files
     *
     * @param  mixed $id
     * @return void
     */
    public function rollDown(mixed $id): void
    {
        $files = $this->fileRepository->getModel()->newQuery()->where('origin_id', $id)->cursor();

        foreach ($files as $file) {
            if (!$file instanceof StorageFile) {
                continue;
            }

            $file->rollDown();
        }
    }

    /**
     * @param  int                     $id
     * @return Collection<StorageFile>
     */
    public function getByOriginals(int $id): Collection
    {
        return LoadReduce::remember(
            sprintf('files::files(%s)', $id),
            fn() => $this->fileRepository->findWhere(['origin_id' => $id])
        );
    }

    /**
     * @todo: Implementation?
     */
    public function deleteAll(mixed $fileId): void {}

    /**
     * @todo: Implementation?
     */
    public function deleteFile(mixed $fileId, ?string $variant): void {}

    /**
     * @param  string     $name
     * @return Filesystem
     */
    public function disk(string $name): Filesystem
    {
        return Storage::disk($name);
    }

    /**
     * @param  mixed  $diskId
     * @return string
     */
    public function getTarget(mixed $diskId): string
    {
        if (!$diskId) {
            $diskId = 'default';
        }

        $config = config('filesystems.disks.' . $diskId . '.target');

        return $config ?? $diskId;
    }

    public function getFile(mixed $id): ?StorageFile
    {
        try {
            return LoadReduce::remember(
                sprintf('files::fileById(%s)', $id),
                fn() => $this->fileRepository->find($id)
            );
        } catch (\Throwable) {
        }

        return null;
    }

    // roll up from temporary file.
    public function rollUp(mixed $id, mixed $storageId): void
    {
        /** @var Collection<StorageFile> $files */
        $files    = $this->fileRepository->getModel()->newQuery()->where('origin_id', $id)->get();
        $configId = $this->getTarget($storageId);
        foreach ($files as $file) {
            $file->up($storageId, $configId);
        }
    }

    /**
     * Create a new storage.
     *
     * @param  string                                           $name
     * @return void
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function removeTap(string $name): void
    {
        $item = $this->diskRepository->findByField('name', $name)->first();

        if (!$item) {
            return;
        }

        $item->delete();
    }

    /**
     * Create a new storage.
     *
     * @param  string                                           $name
     * @param  array                                            $attributes ['name'=>string, 'target_id'=>string]
     * @return void
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function tap(string $name, array $attributes = []): void
    {
        $item = $this->diskRepository->findByField('name', $name)->first();

        if (!$item) {
            $attributes = array_merge([
                'name'   => $name,
                'target' => 'public',
            ], $attributes);

            $fields = $this->diskRepository->getModel()->getFillable();

            $item = $this->diskRepository->create(Arr::only($attributes, $fields));
        }

        $item->save();
    }

    /**
     * Get file content.
     *
     * @param  int|StorageFile $id
     * @return string
     */
    public function get(int|StorageFile $id): string
    {
        $file = is_int($id) ? $this->getFile($id) : $id;

        return Storage::disk($file->target)->get($file->path);
    }

    /**
     * Get content of storage file to temporary file and return its path.
     *
     * @param  int|StorageFile $file Storage file id
     * @return string          Path of temporary file.
     */
    public function getAs(int|StorageFile $file): string
    {
        $storageFile = is_int($file) ? $this->getFile($file) : $file;

        if (!extension_loaded('curl') && '1' !== ini_get('allow_url_fopen')) {
            throw new \RuntimeException('Missed both "curl" extension and allow_url_fopen');
        }

        $tempFile = sprintf('%s_%s', tempnam(sys_get_temp_dir(), 'metafox'), $storageFile->original_name);

        $isLocalFile = config("filesystems.{$storageFile->target}.driver") === 'local';

        try {
            match ($isLocalFile || ini_get('allow_url_fopen')) {
                true    => stream_download_file($tempFile, Storage::disk($storageFile->target)->readStream($storageFile->path)),
                default => curl_download_file($tempFile, $storageFile->url),
            };
        } catch (\Throwable $error) {
            logger()->error($error->getMessage());
            logger()->error($error->getTraceAsString());
        }

        //  remove file before terminating.
        RequestLifecycle::onTerminated(function () use ($tempFile) {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        });

        return $tempFile;
    }

    public function asUploadedFile(mixed $id): UploadedFile
    {
        $file = $this->getFile($id);

        $tempFile = sprintf('%s_%s', tempnam(sys_get_temp_dir(), 'metafox'), $file->original_name);

        file_put_contents($tempFile, Storage::disk($file->target)->get($file->path));

        if (is_resource($tempFile)) {
            fclose($tempFile);
        }

        //  remove file before terminating.
        RequestLifecycle::onTerminated(function () use ($tempFile) {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        });

        return new UploadedFile($tempFile, $file->original_name, $file->mime_type, null, true);
    }

    /**
     * Write the contents of a file.
     *
     * @param  string                                            $storage
     * @param  string                                            $path
     * @param  StreamInterface|File|UploadedFile|string|resource $contents
     * @param  mixed                                             $options
     * @return string|bool
     */
    public function put(string $storage, string $path, $contents, array $options = []): StorageFile
    {
        return $this->disk($storage)->put($path, $contents, $options);
    }

    /**
     * Put a local file to cloud storage.
     *
     * @param  string              $storageId Storage id. Example "photo", "video", "attachment", ...
     * @param  string              $path      Could storage directory etc: "photo/2012/06/14"
     * @param  string|UploadedFile $file      Realpath of local
     * @param  string              $name      Realpath of
     * @param  array               $extra     Extra information of storage file to storage_files table
     * @param  array               $options   Storage options, etc: ['visibility'=> 'public']
     * @return StorageFile
     */
    public function putFileAs(
        string $storageId,
        string $path,
        string|UploadedFile $file,
        string $name,
        array $extra = [],
        array $options = ['visibility' => 'public']
    ): StorageFile {
        $disk     = $this->disk($storageId);
        $newPath  = $disk->putFileAs($path, $file, $name, $options);
        $realpath = is_string($file) ? $file : $file->getPathname();
        $helper   = app('storage.path');

        if (!$newPath) {
            throw new \InvalidArgumentException(sprintf('Could not put "%s" to "%s"', $file, $storageId));
        } else {
            Log::channel('dev')->info('storage putFileAs ' . $disk->url($newPath));
        }

        $fileSize = is_string($file) ? filesize($file) : $file->getSize();

        $attributes = array_merge([
            'storage_id'    => $storageId,
            'file_size'     => $fileSize,
            'original_name' => $helper->getOriginalName($file),
            'extension'     => $helper->getExtension($file),
            'path'          => $newPath,
            'target'        => $this->getTarget($storageId),

        ], $extra);

        $mime_type = MimeTypes::getDefault()->guessMimeType($realpath);
        if ($mime_type) {
            $attributes['mime_type'] = $mime_type;
        }

        $extension = FacadesFile::extension($realpath);
        if ($extension) {
            $attributes['extension'] = $extension;
        }

        if ($mime_type && preg_match('/^image\//', $mime_type) && $imageSize = getimagesize($realpath)) {
            $attributes['width']  = $imageSize[0];
            $attributes['height'] = $imageSize[1];
        }

        return $this->createFile($attributes);
    }

    public function createFile(array $attributes): StorageFile
    {
        $storageFile = new StorageFile($attributes);

        $storageFile->save();

        $storageFile->refresh();

        return $storageFile;
    }

    /**
     * Store the uploaded file on the disk.
     *
     * @param  string                                                     $storage
     * @param  string                                                     $path
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string $file
     * @param  mixed                                                      $options
     * @return string|false
     */
    public function putFile($storage, $path, $file, $options = []): string|false
    {
        return $this->disk($storage)->putFile($path, $file, $options);
    }

    /**
     * Create a streamed download response for a given file.
     *
     * @param  int|StorageFile                                    $file
     * @param  string|null                                        $name
     * @param  array                                              $headers
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(int|StorageFile $file, string $name = null, array $headers = []): StreamedResponse
    {
        $model = is_int($file) ? $this->getFile($file) : $file;

        return $this->disk($model)->download($model->path, $name, $headers);
    }

    /**
     * @param  StorageFile      $from
     * @param  StorageFile|null $origin
     * @return StorageFile|null
     */
    private function copyFile(StorageFile $from, ?StorageFile $origin = null): ?StorageFile
    {
        $copyToPath = app('storage.path')->copyPath($from->path);

        Log::channel('dev')->warning('copy file', compact('from', 'copyToPath'));

        $result = $this->disk($from->target)->copy($from->path, $copyToPath);
        if (!$result) {
            return null;
        }

        // clone size to new size
        $attributes = Arr::except(
            $from->toArray(),
            ['item_id', 'origin_id', 'item_type', 'id', 'user_id', 'user_type']
        );

        $attributes['origin_id'] = $origin?->id;

        return $this->createFile($attributes);
    }

    /**
     * @param  int         $id
     * @return StorageFile
     */
    public function getOrigin(int $id): StorageFile
    {
        /** @var ?StorageFile $file */
        $file = $this->fileRepository->findOrFail($id);

        return $file;
    }

    public function copy(int|StorageFile $file): StorageFile
    {
        $id = is_int($file) ? $file : $file->id;

        $files = $this->getByOriginals($id);

        $origin = $files->first(function (StorageFile $item) {
            return $item->isOrigin();
        });

        $variants = $files->filter(function (StorageFile $item) use ($id) {
            return $item->id !== $id;
        });

        // did not find origin ?
        $copyOrigin = $this->copyFile($origin);

        foreach ($variants as $variant) {
            // copy remote path
            $this->copyFile($variant, $copyOrigin);
        }

        return $copyOrigin;
    }

    /**
     * @param  array $idMap
     * @param  mixed $itemId
     * @param  mixed $itemType
     * @return void
     */
    public function attach(array $idMap, mixed $itemId, mixed $itemType): void
    {
        /** @var StorageFile[] $files */
        $files = $this->fileRepository->getModel()
            ->newQuery()
            ->whereIn('origin_id', array_keys($idMap))
            ->get();

        foreach ($files as $file) {
            if (
                $file->item_id == $itemId
                && $file->item_type == $itemType
            ) {
                continue;
            }

            // todo move from configure A. to configure B.
            $storage   = $idMap[$file->origin_id] ?? 'photo';
            $newConfig = $this->getTarget($storage);
            // should migrate
            if ($file->target !== $newConfig) {
                $file->target = $newConfig;
            }

            if ($storage !== $file->storage_id) {
                $file->storage_id = $storage;
            }

            $file->item_id   = $itemId;
            $file->item_type = $itemType;

            $file->saveQuietly();
        }
    }

    public function getExt(int $id): ?string
    {
        $file = $this->getFile($id);

        return $file?->extension;
    }

    public function getMimeType(?int $id): ?string
    {
        if (null === $id) {
            return null;
        }

        $file = $this->fileRepository->getModel()
            ->newQuery()
            ->where('id', '=', $id)
            ->first();

        if (null === $file) {
            return null;
        }

        return $file->mime_type;
    }
}
