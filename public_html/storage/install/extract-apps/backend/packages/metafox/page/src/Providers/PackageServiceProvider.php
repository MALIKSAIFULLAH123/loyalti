<?php

namespace MetaFox\Page\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Page\Contracts\PageClaimContract;
use MetaFox\Page\Contracts\PageContract;
use MetaFox\Page\Contracts\PageMembershipInterface;
use MetaFox\Page\Models\Block;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageClaim;
use MetaFox\Page\Models\PageHistory;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Models\PageText;
use MetaFox\Page\Observers\BlockObserver;
use MetaFox\Page\Observers\PageCategoryObserver;
use MetaFox\Page\Observers\PageInviteObserver;
use MetaFox\Page\Observers\PageMemberObserver;
use MetaFox\Page\Observers\PageObserver;
use MetaFox\Page\Repositories\ActivityRepositoryInterface;
use MetaFox\Page\Repositories\BlockRepositoryInterface;
use MetaFox\Page\Repositories\Eloquent\ActivityRepository;
use MetaFox\Page\Repositories\Eloquent\BlockRepository;
use MetaFox\Page\Repositories\Eloquent\InfoSettingRepository;
use MetaFox\Page\Repositories\Eloquent\IntegratedModuleRepository;
use MetaFox\Page\Repositories\Eloquent\PageAdminRepository;
use MetaFox\Page\Repositories\Eloquent\PageCategoryRepository;
use MetaFox\Page\Repositories\Eloquent\PageClaimRepository;
use MetaFox\Page\Repositories\Eloquent\PageHistoryRepository;
use MetaFox\Page\Repositories\Eloquent\PageInviteRepository;
use MetaFox\Page\Repositories\Eloquent\PageMemberRepository;
use MetaFox\Page\Repositories\Eloquent\PageRepository;
use MetaFox\Page\Repositories\InfoSettingRepositoryInterface;
use MetaFox\Page\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Page\Repositories\PageAdminRepositoryInterface;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Page\Repositories\PageClaimRepositoryInterface;
use MetaFox\Page\Repositories\PageHistoryRepositoryInterface;
use MetaFox\Page\Repositories\PageInviteRepositoryInterface;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Page\Support\PageClaimSupport;
use MetaFox\Page\Support\PageMembership;
use MetaFox\Page\Support\PageSupport;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * Class PackageServiceProvider.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        PageInviteRepositoryInterface::class       => PageInviteRepository::class,
        PageRepositoryInterface::class             => PageRepository::class,
        PageCategoryRepositoryInterface::class     => PageCategoryRepository::class,
        PageMemberRepositoryInterface::class       => PageMemberRepository::class,
        PageMembershipInterface::class             => PageMembership::class,
        PageContract::class                        => PageSupport::class,
        PageClaimContract::class                   => PageClaimSupport::class,
        BlockRepositoryInterface::class            => BlockRepository::class,
        PageClaimRepositoryInterface::class        => PageClaimRepository::class,
        IntegratedModuleRepositoryInterface::class => IntegratedModuleRepository::class,
        InfoSettingRepositoryInterface::class      => InfoSettingRepository::class,
        PageHistoryRepositoryInterface::class      => PageHistoryRepository::class,
        ActivityRepositoryInterface::class         => ActivityRepository::class,
        PageAdminRepositoryInterface::class        => PageAdminRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Page::ENTITY_TYPE       => Page::class,
            PageMember::ENTITY_TYPE => PageMember::class,
            PageInvite::ENTITY_TYPE => PageInvite::class,
            Block::ENTITY_TYPE      => Block::class,
            PageClaim::ENTITY_TYPE  => PageClaim::class,
        ]);

        Page::observe([EloquentModelObserver::class, PageObserver::class]);
        PageText::observe([EloquentModelObserver::class]);
        PageMember::observe([PageMemberObserver::class, EloquentModelObserver::class]);
        PageClaim::observe([EloquentModelObserver::class]);
        PageInvite::observe([PageInviteObserver::class, EloquentModelObserver::class]);
        Block::observe([EloquentModelObserver::class, BlockObserver::class]);
        Category::observe([EloquentModelObserver::class, PageCategoryObserver::class]);
        PageHistory::observe([EloquentModelObserver::class]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->callAfterResolving('reducer', function ($reducer) {
            $reducer->register([
                \MetaFox\Page\Support\LoadMissingIsUserInvited::class,
                \MetaFox\Page\Support\LoadMissingIsUserBlocked::class,
                \MetaFox\Page\Support\LoadMissingIsPendingInvite::class,
            ]);
        });
    }
}
