<?php

namespace MetaFox\Poll\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Platform\Support\EloquentModelObserver;
use MetaFox\Poll\Contracts\PollSupportInterface;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Models\PollText;
use MetaFox\Poll\Models\Result;
use MetaFox\Poll\Observers\AnswerObserver;
use MetaFox\Poll\Observers\PollObserver;
use MetaFox\Poll\Observers\ResultObserver;
use MetaFox\Poll\Repositories\Eloquent\PollAdminRepository;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;
use MetaFox\Poll\Repositories\Eloquent\ResultRepository;
use MetaFox\Poll\Repositories\PollAdminRepositoryInterface;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\Poll\Repositories\ResultRepositoryInterface;
use MetaFox\Poll\Support\PollSupport;

class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        PollAdminRepositoryInterface::class => PollAdminRepository::class,
        PollRepositoryInterface::class      => PollRepository::class,
        ResultRepositoryInterface::class    => ResultRepository::class,
        PollSupportInterface::class         => PollSupport::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Poll::ENTITY_TYPE   => Poll::class,
            Result::ENTITY_TYPE => Result::class,
        ]);
        Poll::observe([EloquentModelObserver::class, PollObserver::class]);
        PollText::observe([EloquentModelObserver::class]);
        Result::observe([EloquentModelObserver::class, ResultObserver::class]);
        Answer::observe([AnswerObserver::class]);
    }
}
