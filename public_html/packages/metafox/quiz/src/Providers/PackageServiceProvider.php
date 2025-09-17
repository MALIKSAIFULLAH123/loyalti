<?php

namespace MetaFox\Quiz\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Platform\Support\EloquentModelObserver;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\QuizText;
use MetaFox\Quiz\Models\Result;
use MetaFox\Quiz\Observers\QuestionObserver;
use MetaFox\Quiz\Observers\QuizObserver;
use MetaFox\Quiz\Observers\ResultObserver;
use MetaFox\Quiz\Repositories\Eloquent\QuestionRepository;
use MetaFox\Quiz\Repositories\Eloquent\QuizAdminRepository;
use MetaFox\Quiz\Repositories\Eloquent\QuizRepository;
use MetaFox\Quiz\Repositories\Eloquent\ResultRepository;
use MetaFox\Quiz\Repositories\QuestionRepositoryInterface;
use MetaFox\Quiz\Repositories\QuizAdminRepositoryInterface;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use MetaFox\Quiz\Repositories\ResultRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        QuizRepositoryInterface::class      => QuizRepository::class,
        QuizAdminRepositoryInterface::class => QuizAdminRepository::class,
        ResultRepositoryInterface::class    => ResultRepository::class,
        QuestionRepositoryInterface::class  => QuestionRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Quiz::ENTITY_TYPE => Quiz::class,
        ]);

        Quiz::observe([EloquentModelObserver::class, QuizObserver::class]);
        QuizText::observe([EloquentModelObserver::class]);
        Question::observe([QuestionObserver::class]);
        Result::observe([ResultObserver::class, EloquentModelObserver::class]);
    }
}
