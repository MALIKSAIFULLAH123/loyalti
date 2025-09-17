<?php

namespace MetaFox\GettingStarted\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\GettingStarted\Models\TodoList;
use MetaFox\GettingStarted\Models\TodoListImage;
use MetaFox\GettingStarted\Models\TodoListText;
use MetaFox\GettingStarted\Models\TodoListView;
use MetaFox\GettingStarted\Observers\TodoListImageObserver;
use MetaFox\GettingStarted\Observers\TodoListObserver;
use MetaFox\GettingStarted\Observers\TodoListTextObserver;
use MetaFox\GettingStarted\Repositories\Eloquent\TodoListImageRepository;
use MetaFox\GettingStarted\Repositories\Eloquent\TodoListRepository;
use MetaFox\GettingStarted\Repositories\Eloquent\TodoListTextRepository;
use MetaFox\GettingStarted\Repositories\Eloquent\TodoListViewRepository;
use MetaFox\GettingStarted\Repositories\Eloquent\UserFirstLoginRepository;
use MetaFox\GettingStarted\Repositories\TodoListImageRepositoryInterface;
use MetaFox\GettingStarted\Repositories\TodoListRepositoryInterface;
use MetaFox\GettingStarted\Repositories\TodoListTextRepositoryInterface;
use MetaFox\GettingStarted\Repositories\TodoListViewRepositoryInterface;
use MetaFox\GettingStarted\Repositories\UserFirstLoginRepositoryInterface;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Providers/PackageServiceProvider.stub.
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
        TodoListRepositoryInterface::class       => TodoListRepository::class,
        TodoListTextRepositoryInterface::class   => TodoListTextRepository::class,
        TodoListImageRepositoryInterface::class  => TodoListImageRepository::class,
        TodoListViewRepositoryInterface::class   => TodoListViewRepository::class,
        UserFirstLoginRepositoryInterface::class => UserFirstLoginRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            TodoList::ENTITY_TYPE => TodoList::class,
        ]);

        TodoList::observe([EloquentModelObserver::class, TodoListObserver::class]);
        TodoListText::observe([EloquentModelObserver::class, TodoListTextObserver::class]);
        TodoListImage::observe([TodoListImageObserver::class]);
    }
}
