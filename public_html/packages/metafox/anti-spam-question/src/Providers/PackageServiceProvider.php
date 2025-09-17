<?php

namespace MetaFox\AntiSpamQuestion\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\AntiSpamQuestion\Models\Question;
use MetaFox\AntiSpamQuestion\Observers\QuestionObserver;
use MetaFox\AntiSpamQuestion\Repositories\AnswerAdminRepositoryInterface;
use MetaFox\AntiSpamQuestion\Repositories\Eloquent\AnswerAdminRepository;
use MetaFox\AntiSpamQuestion\Repositories\Eloquent\QuestionAdminRepository;
use MetaFox\AntiSpamQuestion\Repositories\QuestionAdminRepositoryInterface;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Providers/PackageServiceProvider.stub
 */

/**
 * Class PackageServiceProvider.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        QuestionAdminRepositoryInterface::class => QuestionAdminRepository::class,
        AnswerAdminRepositoryInterface::class   => AnswerAdminRepository::class,
    ];

    public function boot()
    {
        Question::observe([EloquentModelObserver::class, QuestionObserver::class]);
    }
}
