<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MetaFox\Core\Jobs\UpdateAdminSearch;

class UpdateAdminSearchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metafox:update-admin-search {--sync : Perform the operation in sync mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update admin search entries';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if ($this->option('sync')) {
            $locale = app()->getLocale();

            UpdateAdminSearch::dispatchSync();

            // keep old locale because of request
            if ($locale && $locale != app()->getLocale()) {
                app()->setLocale($locale);
            }

            return 0;
        }

        UpdateAdminSearch::dispatch();

        return 0;
    }
}
