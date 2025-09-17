<?php

namespace MetaFox\Photo\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Photo\Contracts\AlbumContract;
use MetaFox\Photo\Contracts\PhotoGroupSupportContract;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\AlbumItem;
use MetaFox\Photo\Models\AlbumText;
use MetaFox\Photo\Models\Category;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Photo\Models\PhotoInfo;
use MetaFox\Photo\Observers\AlbumItemObserver;
use MetaFox\Photo\Observers\AlbumObserver;
use MetaFox\Photo\Observers\PhotoGroupItemObserver;
use MetaFox\Photo\Observers\PhotoGroupObserver;
use MetaFox\Photo\Observers\PhotoObserver;
use MetaFox\Photo\Repositories\AlbumAdminRepositoryInterface;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Repositories\CategoryRepositoryInterface;
use MetaFox\Photo\Repositories\Eloquent\AlbumAdminRepository;
use MetaFox\Photo\Repositories\Eloquent\AlbumRepository;
use MetaFox\Photo\Repositories\Eloquent\CategoryRepository;
use MetaFox\Photo\Repositories\Eloquent\PhotoAdminRepository;
use MetaFox\Photo\Repositories\Eloquent\PhotoGroupRepository;
use MetaFox\Photo\Repositories\Eloquent\PhotoRepository;
use MetaFox\Photo\Repositories\PhotoAdminRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Photo\Support\Album as SupportAlbum;
use MetaFox\Photo\Support\PhotoGroup as PhotoGroupSupport;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * Class PhotoServiceProvider.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        'Photo'                              => \MetaFox\Photo\Support\Photo::class,
        PhotoRepositoryInterface::class      => PhotoRepository::class,
        AlbumRepositoryInterface::class      => AlbumRepository::class,
        CategoryRepositoryInterface::class   => CategoryRepository::class,
        PhotoGroupRepositoryInterface::class => PhotoGroupRepository::class,
        AlbumContract::class                 => SupportAlbum::class,
        PhotoGroupSupportContract::class     => PhotoGroupSupport::class,
        PhotoAdminRepositoryInterface::class => PhotoAdminRepository::class,
        AlbumAdminRepositoryInterface::class => AlbumAdminRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Photo::ENTITY_TYPE      => Photo::class,
            Album::ENTITY_TYPE      => Album::class,
            PhotoGroup::ENTITY_TYPE => PhotoGroup::class,
        ]);

        PhotoGroup::observe([PhotoGroupObserver::class, EloquentModelObserver::class]);
        Photo::observe([PhotoObserver::class, EloquentModelObserver::class]);
        PhotoInfo::observe([EloquentModelObserver::class]);
        Album::observe([EloquentModelObserver::class, AlbumObserver::class]);
        PhotoGroupItem::observe([PhotoGroupItemObserver::class, EloquentModelObserver::class]);
        AlbumItem::observe([AlbumItemObserver::class]);
        AlbumText::observe([EloquentModelObserver::class]);
        Category::observe([EloquentModelObserver::class]);
    }

    public function register()
    {
        $this->callAfterResolving('reducer', function ($reducer) {
            $reducer->register([
                \MetaFox\Photo\Support\LoadMissingFeedPhotos::class,
                \MetaFox\Photo\Support\LoadMissingAlbumItems::class,
                \MetaFox\Photo\Support\LoadMissingAlbumApprovedItems::class,
            ]);
        });
    }
}
