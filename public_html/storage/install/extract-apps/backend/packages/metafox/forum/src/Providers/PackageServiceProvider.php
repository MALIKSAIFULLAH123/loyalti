<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Forum\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Forum\Contracts\ForumPostSupportContract;
use MetaFox\Forum\Contracts\ForumSupportContract;
use MetaFox\Forum\Contracts\ForumThreadSupportContract;
use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumPostQuote;
use MetaFox\Forum\Models\ForumPostText;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Models\ForumThreadSubscribe;
use MetaFox\Forum\Models\ForumThreadText;
use MetaFox\Forum\Models\Moderator;
use MetaFox\Forum\Models\ModeratorAccess;
use MetaFox\Forum\Observers\ForumObserver;
use MetaFox\Forum\Observers\ForumPostObserver;
use MetaFox\Forum\Observers\ForumThreadObserver;
use MetaFox\Forum\Observers\ModeratorObserver;
use MetaFox\Forum\Repositories\Eloquent\ForumAdminRepository;
use MetaFox\Forum\Repositories\Eloquent\ForumPostAdminRepository;
use MetaFox\Forum\Repositories\Eloquent\ForumPostRepository;
use MetaFox\Forum\Repositories\Eloquent\ForumRepository;
use MetaFox\Forum\Repositories\Eloquent\ForumThreadAdminRepository;
use MetaFox\Forum\Repositories\Eloquent\ForumThreadLastReadRepository;
use MetaFox\Forum\Repositories\Eloquent\ForumThreadRepository;
use MetaFox\Forum\Repositories\Eloquent\ForumThreadSubscribeRepository;
use MetaFox\Forum\Repositories\Eloquent\ModeratorRepository;
use MetaFox\Forum\Repositories\Eloquent\PermissionConfigRepository;
use MetaFox\Forum\Repositories\Eloquent\UserRolePermissionRepository;
use MetaFox\Forum\Repositories\ForumAdminRepositoryInterface;
use MetaFox\Forum\Repositories\ForumPostAdminRepositoryInterface;
use MetaFox\Forum\Repositories\ForumPostRepositoryInterface;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadAdminRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadLastReadRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadSubscribeRepositoryInterface;
use MetaFox\Forum\Repositories\ModeratorRepositoryInterface;
use MetaFox\Forum\Repositories\PermissionConfigRepositoryInterface;
use MetaFox\Forum\Repositories\UserRolePermissionRepositoryInterface;
use MetaFox\Forum\Support\ForumPostSupport;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Forum\Support\ForumThreadSupport;
use MetaFox\Platform\Support\EloquentModelObserver;

class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        ForumRepositoryInterface::class                => ForumRepository::class,
        ForumAdminRepositoryInterface::class           => ForumAdminRepository::class,
        ForumThreadRepositoryInterface::class          => ForumThreadRepository::class,
        ForumPostRepositoryInterface::class            => ForumPostRepository::class,
        ForumThreadAdminRepositoryInterface::class     => ForumThreadAdminRepository::class,
        ForumPostAdminRepositoryInterface::class       => ForumPostAdminRepository::class,
        ForumThreadSubscribeRepositoryInterface::class => ForumThreadSubscribeRepository::class,
        ForumPostSupportContract::class                => ForumPostSupport::class,
        ForumThreadLastReadRepositoryInterface::class  => ForumThreadLastReadRepository::class,
        ForumSupportContract::class                    => ForumSupport::class,
        ForumThreadSupportContract::class              => ForumThreadSupport::class,
        ModeratorRepositoryInterface::class            => ModeratorRepository::class,
        PermissionConfigRepositoryInterface::class     => PermissionConfigRepository::class,
        UserRolePermissionRepositoryInterface::class   => UserRolePermissionRepository::class,
    ];

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            ForumThread::ENTITY_TYPE     => ForumThread::class,
            ForumPost::ENTITY_TYPE       => ForumPost::class,
            Moderator::ENTITY_TYPE       => Moderator::class,
            ModeratorAccess::ENTITY_TYPE => ModeratorAccess::class,
            'forum_post_quote'           => ForumPostQuote::class,
            'forum_thread_subscribe'     => ForumThreadSubscribe::class,
        ]);

        Forum::observe([EloquentModelObserver::class, ForumObserver::class]);
        ForumPostText::observe([EloquentModelObserver::class]);
        ForumThreadText::observe([EloquentModelObserver::class]);
        ForumThread::observe([EloquentModelObserver::class, ForumThreadObserver::class]);
        ForumPost::observe([EloquentModelObserver::class, ForumPostObserver::class]);
        Moderator::observe([ModeratorObserver::class]);
    }
}
