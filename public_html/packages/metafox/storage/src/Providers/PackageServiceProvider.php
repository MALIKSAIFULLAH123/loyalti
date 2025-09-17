<?php

namespace MetaFox\Storage\Providers;

use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Platform\Support\EloquentModelObserver;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Storage\Observers\StorageFileObserver;
use MetaFox\Storage\Repositories\AssetRepositoryInterface;
use MetaFox\Storage\Repositories\DiskRepositoryInterface;
use MetaFox\Storage\Repositories\Eloquent\AssetRepository;
use MetaFox\Storage\Repositories\Eloquent\DiskRepository;
use MetaFox\Storage\Repositories\Eloquent\FileRepository;
use MetaFox\Storage\Repositories\FileRepositoryInterface;
use MetaFox\Storage\Support\PathGenerator;
use MetaFox\Storage\Support\StorageService;

class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        'storage'                       => StorageService::class,
        'asset'                         => AssetRepository::class,
        'storage.path'                  => PathGenerator::class,
        FileRepositoryInterface::class  => FileRepository::class,
        DiskRepositoryInterface::class  => DiskRepository::class,
        AssetRepositoryInterface::class => AssetRepository::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        Relation::morphMap([
            StorageFile::ENTITY_TYPE => StorageFile::class,
        ]);

        $this->callAfterResolving('reducer', function ($reducer) {
            $reducer->register([
                \MetaFox\Storage\Support\LoadMissingStorageFiles::class,
            ]);
        });
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        StorageFile::observe([StorageFileObserver::class]);
    }
}
