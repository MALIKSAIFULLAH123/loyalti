<?php

namespace MetaFox\Storage\Repositories;

use MetaFox\Storage\Models\Asset;
use MetaFox\Storage\Models\StorageFile;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Asset.
 *
 * @mixin BaseRepository
 * @method Asset getModel()
 * @method Asset find($id, $columns = ['*'])
 */
interface AssetRepositoryInterface
{
    /**
     * publishAssets.
     *
     * @param  string $package
     * @return void
     */
    public function publishAssets(string $package): void;

    /**
     * loadAssetSettings.
     *
     * @return array<string>
     */
    public function loadAssetSettings(): array;

    /**
     * findAssetByName.
     *
     * @param  string $name
     * @return ?Asset
     */
    public function findByName(string $name): ?Asset;

    /**
     * @param  int              $id
     * @return StorageFile|null
     */
    public function getDefaultAssetFile(int $id): ?StorageFile;

    /**
     * @param int                  $id
     * @param array<string, mixed> $attributes
     */
    public function restoreDefaultAsset(int $id, array $attributes = []): Asset;
}
