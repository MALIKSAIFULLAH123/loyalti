<?php

namespace MetaFox\Sticker\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Sticker\Models\Sticker;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Models\StickerUserValue;
use MetaFox\Sticker\Observers\StickerObserver;
use MetaFox\Sticker\Observers\StickerRecentObserver;
use MetaFox\Sticker\Observers\StickerSetObserver;
use MetaFox\Sticker\Observers\StickerUserValueObserver;
use MetaFox\Sticker\Repositories\Eloquent\StickerRecentRepository;
use MetaFox\Sticker\Repositories\Eloquent\StickerRepository;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetAdminRepository;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetRepository;
use MetaFox\Sticker\Repositories\StickerRecentRepositoryInterface;
use MetaFox\Sticker\Repositories\StickerRepositoryInterface;
use MetaFox\Sticker\Repositories\StickerSetAdminRepositoryInterface;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;

/**
 * Class PackageServiceProvider.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        StickerSetRepositoryInterface::class      => StickerSetRepository::class,
        StickerSetAdminRepositoryInterface::class => StickerSetAdminRepository::class,
        StickerRepositoryInterface::class         => StickerRepository::class,
        StickerRecentRepositoryInterface::class   => StickerRecentRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Sticker::ENTITY_TYPE => Sticker::class,
        ]);
        StickerSet::observe([StickerSetObserver::class]);
        Sticker::observe([StickerObserver::class]);
        StickerUserValue::observe([StickerUserValueObserver::class]);
    }
}
