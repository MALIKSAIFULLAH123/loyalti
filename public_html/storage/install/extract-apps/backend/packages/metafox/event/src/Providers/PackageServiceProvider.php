<?php

namespace MetaFox\Event\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Event\Contracts\EventContract;
use MetaFox\Event\Contracts\EventInviteContract;
use MetaFox\Event\Contracts\EventMembershipContract;
use MetaFox\Event\Contracts\ExporterContract;
use MetaFox\Event\Models\Category;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\EventText;
use MetaFox\Event\Models\HostInvite;
use MetaFox\Event\Models\Invite;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Observers\EventObserver;
use MetaFox\Event\Observers\HostInviteObserver;
use MetaFox\Event\Observers\InviteObserver;
use MetaFox\Event\Observers\MemberObserver;
use MetaFox\Event\Repositories\CategoryRepositoryInterface;
use MetaFox\Event\Repositories\Eloquent\CategoryRepository;
use MetaFox\Event\Repositories\Eloquent\EventAdminRepository;
use MetaFox\Event\Repositories\Eloquent\EventRepository;
use MetaFox\Event\Repositories\Eloquent\HostInviteRepository;
use MetaFox\Event\Repositories\Eloquent\InviteCodeRepository;
use MetaFox\Event\Repositories\Eloquent\InviteRepository;
use MetaFox\Event\Repositories\Eloquent\MemberRepository;
use MetaFox\Event\Repositories\EventAdminRepositoryInterface;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Event\Repositories\HostInviteRepositoryInterface;
use MetaFox\Event\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Event\Repositories\InviteRepositoryInterface;
use MetaFox\Event\Repositories\MemberRepositoryInterface;
use MetaFox\Event\Support\Event as SupportEvent;
use MetaFox\Event\Support\EventInvite;
use MetaFox\Event\Support\EventMembership;
use MetaFox\Event\Support\Exporter;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        EventRepositoryInterface::class      => EventRepository::class,
        EventAdminRepositoryInterface::class => EventAdminRepository::class,
        CategoryRepositoryInterface::class   => CategoryRepository::class,
        InviteRepositoryInterface::class     => InviteRepository::class,
        HostInviteRepositoryInterface::class => HostInviteRepository::class,
        MemberRepositoryInterface::class     => MemberRepository::class,
        InviteCodeRepositoryInterface::class => InviteCodeRepository::class,
        EventContract::class                 => SupportEvent::class,
        EventMembershipContract::class       => EventMembership::class,
        EventInviteContract::class           => EventInvite::class,
        ExporterContract::class              => Exporter::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Event::ENTITY_TYPE      => Event::class,
            Invite::ENTITY_TYPE     => Invite::class,
            HostInvite::ENTITY_TYPE => HostInvite::class,
            Member::ENTITY_TYPE     => Member::class,
        ]);

        Event::observe([EloquentModelObserver::class, EventObserver::class]);
        EventText::observe([EloquentModelObserver::class]);
        Member::observe([EloquentModelObserver::class, MemberObserver::class]);
        Invite::observe([EloquentModelObserver::class, InviteObserver::class]);
        HostInvite::observe([EloquentModelObserver::class, HostInviteObserver::class]);
        Category::observe([EloquentModelObserver::class]);
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
                \MetaFox\Event\Support\LoadMissingMember::class,
                \MetaFox\Event\Support\LoadMissingInvite::class,
                \MetaFox\Event\Support\LoadMissingTotalPendingInvite::class,
            ]);
        });
    }
}
