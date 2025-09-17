<?php

namespace Foxexpert\Sevent\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Models\Ticket;
use Foxexpert\Sevent\Models\UserTicket;
use Foxexpert\Sevent\Models\Invoice;
use Foxexpert\Sevent\Repositories\Eloquent\InvoiceRepository;
use Foxexpert\Sevent\Repositories\Eloquent\InvoiceTransactionRepository;
use Foxexpert\Sevent\Repositories\InvoiceRepositoryInterface;
use Foxexpert\Sevent\Repositories\InvoiceTransactionRepositoryInterface;
use Foxexpert\Sevent\Models\SeventText;
use Foxexpert\Sevent\Models\Category;
use Foxexpert\Sevent\Observers\SeventObserver;
use Foxexpert\Sevent\Observers\CategoryObserver;
use MetaFox\Platform\Facades\Profiling;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * Class PackageServiceProvider.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        \Foxexpert\Sevent\Repositories\CategoryRepositoryInterface::class => \Foxexpert\Sevent\Repositories\Eloquent\CategoryRepository::class,
        \Foxexpert\Sevent\Repositories\SeventRepositoryInterface::class     => \Foxexpert\Sevent\Repositories\Eloquent\SeventRepository::class,
        \Foxexpert\Sevent\Repositories\ImageRepositoryInterface::class     => \Foxexpert\Sevent\Repositories\Eloquent\ImageRepository::class,
        \Foxexpert\Sevent\Repositories\TicketRepositoryInterface::class     => \Foxexpert\Sevent\Repositories\Eloquent\TicketRepository::class,
        \Foxexpert\Sevent\Repositories\SeventFavouriteRepositoryInterface::class     => \Foxexpert\Sevent\Repositories\Eloquent\SeventFavouriteRepository::class,

        InvoiceRepositoryInterface::class            => InvoiceRepository::class,
        InvoiceTransactionRepositoryInterface::class => InvoiceTransactionRepository::class,
    ];

    /**
     * Boot the application sevents.
     *
     * @return void
     */
    public function boot()
    {
        /*
         * Register relation
         */
        Relation::morphMap([
            Sevent::ENTITY_TYPE => Sevent::class,
            UserTicket::ENTITY_TYPE => UserTicket::class,
            Ticket::ENTITY_TYPE => Ticket::class,
            Invoice::ENTITY_TYPE      => Invoice::class,
        ]);

        Sevent::observe([EloquentModelObserver::class, SeventObserver::class]);
        SeventText::observe([EloquentModelObserver::class]);
        Category::observe([CategoryObserver::class, EloquentModelObserver::class]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Profiling::tick(__METHOD__);
        
        $this->callAfterResolving('reducer', function ($reducer) {
            return $reducer->register([
                \Foxexpert\Sevent\Support\LoadMissingSeventText::class,
            ]);
        });

        Profiling::end(__METHOD__);
    }
}
