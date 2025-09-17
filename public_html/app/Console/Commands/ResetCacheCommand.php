<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use MetaFox\Platform\Facades\Settings;

class ResetCacheCommand extends Command
{
    protected $name = 'cache:reset';

    protected $description = 'Reset all cache';

    public function handle(): int
    {
        $this->components->info('Clearing cached bootstrap files.');

        try {
            Settings::refresh();
            Cache::flush();

            localCacheStore()->clear();

            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
        } catch (Exception $e) {
            // silent
            $this->info($e->getMessage());
        }

        $locale = app()->getLocale();

        collect([
            'permission' => fn () => $this->callSilent('permission:cache-reset') == 0,
            'cache'      => fn () => $this->callSilent('cache:clear') == 0,
            'config'     => fn () => $this->callSilent('config:cache') == 0,
        ])->each(fn ($task, $description) => $this->components->task($description, $task));

        // keep old locale because of request
        if ($locale && $locale != app()->getLocale()) {
            app()->setLocale($locale);
        }

        $this->newLine();

        return Command::SUCCESS;
    }
}
