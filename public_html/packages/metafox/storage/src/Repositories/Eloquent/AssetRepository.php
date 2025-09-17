<?php

namespace MetaFox\Storage\Repositories\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Storage\Models\Asset;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Storage\Repositories\AssetRepositoryInterface;
use MetaFox\Storage\Repositories\FileRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class AssetRepository.
 * @method Asset getModel()
 * @method Asset find($id, $columns = ['*'])
 */
class AssetRepository extends AbstractRepository implements AssetRepositoryInterface
{
    public function model()
    {
        return Asset::class;
    }

    /**
     * Get an array of files in the asset paths.
     *
     * @param  string  $path
     *
     * @return array<string>
     */
    private function listFilesRecursive(string $path): array
    {
        $files = [];

        foreach (File::files($path) as $file) {
            $files[] = $file->getPathname();
        }

        foreach (File::directories($path) as $directory) {
            foreach ($this->listFilesRecursive($directory) as $file) {
                $files[] = $file;
            }
        }

        return $files;
    }

    public function publishAssets(string $package): void
    {
        Log::channel('installation')->debug(sprintf('%s publish assets', $package));

        $assetPath = PackageManager::getAssetPath($package);
        $localRoot = base_path($assetPath);
        $moduleId = PackageManager::getAlias($package);

        // check local file system
        if (!File::isDirectory($localRoot)) {
            Log::channel('installation')->debug(sprintf('%s has no assets', $package));

            return;
        }

        $files = $this->listFilesRecursive($localRoot);

        if (empty($files)) {
            return;
        }

        $copyToDir = $this->getAssetDirPath($package);

        $config = PackageManager::getConfig($package);

        $publishAssets = $config['shareAssets'] ?? [];

        foreach ($files as $file) {
            $localPath = substr($file, strlen($localRoot) + 1);

            $data = Arr::get($publishAssets, $localPath);

            $name = $data;

            $attachFile = true;

            if (is_array($data)) {
                $name = Arr::get($data, 'name');

                $attachFile = (bool)Arr::get($data, 'attach_file', true);
            }

            $exists = $this->getModel()->newQuery()->where([
                'module_id'  => $moduleId,
                'package_id' => $package,
                'name'       => $name,
            ])->first();

            if ($exists) {
                continue;
            }

            $storageFile = null;

            if ($attachFile) {
                $storageFile = app('storage')
                    ->putFileAs('asset', $copyToDir, $file, $localPath, [
                        'item_type' => Asset::ENTITY_TYPE,
                    ]);

                $storageFile->refresh();
            }

            if ($name) {
                $this->getModel()->newQuery()->create([
                    'module_id'  => $moduleId,
                    'package_id' => $package,
                    'local_path' => $attachFile ? $localPath : '',
                    'name'       => $name,
                    'file_id'    => $storageFile?->id,
                ]);
            }
        }
    }

    public function loadAssetSettings(): array
    {
        /** @var Collection<Asset> $rows */
        $rows = $this->getModel()->newQuery()
            ->whereIn('module_id', resolve('core.packages')->getActivePackageAliases())
            ->whereNotNull('name')
            ->cursor();

        $results = [];

        foreach ($rows as $row) {
            Arr::set($results, sprintf('%s.%s', $row->module_id, $row->name), $row->url);
        }

        return $results;
    }

    public function findByName(string $name): ?Asset
    {
        return $this->getModel()->newQuery()
            ->whereIn('module_id', resolve('core.packages')->getActivePackageAliases())
            ->where('name', $name)
            ->first();
    }

    /**
     * @param  strign  $name
     * @return StorageFile|null
     */
    public function getDefaultAssetFile(int $id): ?StorageFile
    {
        $asset = $this->find($id);

        // Find in file storage
        $defaultFile = $this->fileRepository()
            ->getModel()
            ->newModelQuery()
            ->where('storage_id', 'asset')
            ->where('variant', 'origin')
            ->where('path', $this->getAssetDirPath($asset->package_id).'/'.$asset->local_path)
            ->first();

        if (!$defaultFile instanceof StorageFile) {
            return null;
        }

        return $defaultFile;
    }

    /**
     * @inheritDoc
     */
    public function restoreDefaultAsset(int $id, array $attributes = []): Asset
    {
        $asset = $this->find($id);

        if (!$asset->isModified()) {
            return $asset;
        }

        $defaultFileId = Arr::get($attributes, 'default_file_id', 0);

        if (null === $defaultFileId || $defaultFileId > 0) {
            $asset->update(['file_id' => $defaultFileId]);
        }

        return $asset->refresh();
    }

    protected function fileRepository(): FileRepositoryInterface
    {
        return resolve(FileRepositoryInterface::class);
    }

    protected function getAssetDirPath(string $package): string
    {
        $copyToDir = config(sprintf('metafox.packages.%s.asset', $package));

        // use "alias" as assets directory
        if (!$copyToDir) {
            $copyToDir = config(sprintf('metafox.packages.%s.alias', $package));
        }

        return 'assets/'.$copyToDir;
    }

    /**
     * @param  string  $package  package id or package alias
     * @param  string  $name
     * @return string|null
     */
    public function getUrl(string $package, string $name): ?string
    {
        $key = str_contains($package, '/') ? 'package_id' : 'module_id';

        /** @var Asset $row */
        $row =  $this->getModel()->newQuery()
            ->where($key, $package)
            ->where('name', $name)
            ->first();

        return $row?->url;
    }
}
