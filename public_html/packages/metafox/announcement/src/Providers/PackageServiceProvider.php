<?php

namespace MetaFox\Announcement\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Announcement\Contracts\Support\Announcement as AnnouncementSupportContract;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\AnnouncementContent;
use MetaFox\Announcement\Models\AnnouncementView;
use MetaFox\Announcement\Observers\AnnouncementContentObserver;
use MetaFox\Announcement\Observers\AnnouncementObserver;
use MetaFox\Announcement\Observers\AnnouncementViewObserver;
use MetaFox\Announcement\Repositories\AnnouncementCloseRepositoryInterface;
use MetaFox\Announcement\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Announcement\Repositories\AnnouncementViewRepositoryInterface;
use MetaFox\Announcement\Repositories\AnnouncementContentRepositoryInterface;
use MetaFox\Announcement\Repositories\Eloquent\AnnouncementCloseRepository;
use MetaFox\Announcement\Repositories\Eloquent\AnnouncementRepository;
use MetaFox\Announcement\Repositories\Eloquent\AnnouncementViewRepository;
use MetaFox\Announcement\Repositories\Eloquent\AnnouncementContentRepository;
use MetaFox\Announcement\Repositories\Eloquent\HiddenRepository;
use MetaFox\Announcement\Repositories\Eloquent\StyleRepository;
use MetaFox\Announcement\Repositories\HiddenRepositoryInterface;
use MetaFox\Announcement\Repositories\StyleRepositoryInterface;
use MetaFox\Announcement\Support\Announcement as AnnouncementSupport;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * Clas PackageServiceProvider.
 * @ignore
 * @codeCoverageIgnore
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        AnnouncementRepositoryInterface::class        => AnnouncementRepository::class,
        AnnouncementViewRepositoryInterface::class    => AnnouncementViewRepository::class,
        HiddenRepositoryInterface::class              => HiddenRepository::class,
        StyleRepositoryInterface::class               => StyleRepository::class,
        AnnouncementSupportContract::class            => AnnouncementSupport::class,
        AnnouncementCloseRepositoryInterface::class   => AnnouncementCloseRepository::class,
        AnnouncementContentRepositoryInterface::class => AnnouncementContentRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Announcement::ENTITY_TYPE => Announcement::class,
        ]);
        Announcement::observe([AnnouncementObserver::class, EloquentModelObserver::class]);
        AnnouncementView::observe([AnnouncementViewObserver::class]);
        AnnouncementContent::observe([AnnouncementContentObserver::class, EloquentModelObserver::class]);
    }
}
