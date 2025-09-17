<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Facades\Settings;

class OptimizeClearCommand extends Command
{
    protected $name = 'optimize:clear';

    protected $description = 'Clearing cached bootstrap files';

    public function handle(): int
    {
        $this->components->info('Clearing cached bootstrap files.');

        try {
            if (Schema::hasTable('core_site_settings')) {
                Settings::refresh();
            }

            Cache::flush();
            localCacheStore()->clear();

            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
        } catch (Exception $e) {
            // silent
            $this->info($e->getMessage());
        }

        collect([
            'permission:cache-reset' => fn () => $this->callSilent('permission:cache-reset') == 0,
            'event:clear'            => fn () => $this->callSilent('event:clear') == 0,
            'view:clear'             => fn () => $this->callSilent('view:clear') == 0,
            'cache:clear'            => fn () => $this->callSilent('cache:clear') == 0,
            'route:clear'            => fn () => $this->callSilent('route:clear') == 0,
            'config:clear'           => fn () => $this->callSilent('config:clear') == 0,
            'clear-compiled'         => fn () => $this->callSilent('clear-compiled') == 0,
        ])->each(fn ($task, $description) => $this->components->task($description, $task));

        $this->newLine();

        return Command::SUCCESS;
    }
}
