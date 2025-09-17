<?php

namespace MetaFox\Video\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Platform\Support\EloquentModelObserver;
use MetaFox\Video\Contracts\ProviderManagerInterface;
use MetaFox\Video\Contracts\Support\VideoSupportInterface;
use MetaFox\Video\Models\Category;
use MetaFox\Video\Models\CategoryData;
use MetaFox\Video\Models\VerifyProcess;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Models\VideoText;
use MetaFox\Video\Observers\VideoObserver;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;
use MetaFox\Video\Repositories\Eloquent\CategoryRepository;
use MetaFox\Video\Repositories\Eloquent\VerifyProcessRepository;
use MetaFox\Video\Repositories\Eloquent\VideoAdminRepository;
use MetaFox\Video\Repositories\Eloquent\VideoRepository;
use MetaFox\Video\Repositories\VerifyProcessRepositoryInterface;
use MetaFox\Video\Repositories\VideoAdminRepositoryInterface;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use MetaFox\Video\Support\ProviderManager;
use MetaFox\Video\Support\VideoSupport;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        VideoRepositoryInterface::class         => VideoRepository::class,
        VideoAdminRepositoryInterface::class    => VideoAdminRepository::class,
        CategoryRepositoryInterface::class      => CategoryRepository::class,
        ProviderManagerInterface::class         => ProviderManager::class,
        VideoSupportInterface::class            => VideoSupport::class,
        VerifyProcessRepositoryInterface::class => VerifyProcessRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Video::ENTITY_TYPE         => Video::class,
            CategoryData::ENTITY_TYPE  => CategoryData::class,
            VerifyProcess::ENTITY_TYPE => VerifyProcess::class,
        ]);

        Video::observe([EloquentModelObserver::class, VideoObserver::class]);
        VideoText::observe([EloquentModelObserver::class]);
        Category::observe([EloquentModelObserver::class]);
    }
}
