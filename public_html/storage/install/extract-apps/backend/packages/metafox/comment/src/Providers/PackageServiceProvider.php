<?php

namespace MetaFox\Comment\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentAttachment;
use MetaFox\Comment\Models\CommentHistory;
use MetaFox\Comment\Observers\CommentHistoryObserver;
use MetaFox\Comment\Observers\CommentObserver;
use MetaFox\Comment\Repositories\CommentAdminRepositoryInterface;
use MetaFox\Comment\Repositories\CommentHiddenRepositoryInterface;
use MetaFox\Comment\Repositories\CommentHistoryRepositoryInterface;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Comment\Repositories\CommentStatisticRepositoryInterface;
use MetaFox\Comment\Repositories\Eloquent\CommentAdminRepository;
use MetaFox\Comment\Repositories\Eloquent\CommentHiddenRepository;
use MetaFox\Comment\Repositories\Eloquent\CommentHistoryRepository;
use MetaFox\Comment\Repositories\Eloquent\CommentRepository;
use MetaFox\Comment\Repositories\Eloquent\CommentStatisticRepository;
use MetaFox\Platform\Support\EloquentModelObserver;

class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        CommentRepositoryInterface::class          => CommentRepository::class,
        CommentAdminRepositoryInterface::class     => CommentAdminRepository::class,
        CommentHistoryRepositoryInterface::class   => CommentHistoryRepository::class,
        CommentStatisticRepositoryInterface::class => CommentStatisticRepository::class,
        CommentHiddenRepositoryInterface::class    => CommentHiddenRepository::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->callAfterResolving('reducer', function ($reducer) {
            $reducer->register([
                \MetaFox\Comment\Support\LoadMissingDetailRelatedComments::class,
                \MetaFox\Comment\Support\LoadMissingRelatedComments::class,
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
        Relation::morphMap([
            Comment::ENTITY_TYPE => Comment::class,
        ]);

        Comment::observe([
            CommentObserver::class,
            EloquentModelObserver::class,
        ]);

        CommentHistory::observe([CommentHistoryObserver::class]);
        CommentAttachment::observe([EloquentModelObserver::class]);
    }
}
