<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\PackageManager;

class PlatformUpgradeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metafox:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'MetaFox update command';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (!file_exists(base_path('.env'))) {
            $this->error('.env file does not exists');

            return 1;
        }

        Artisan::call('package:discover');

        // Clear cache.
        Artisan::call('optimize:clear');

        // Install bsae database + seeder.
        $this->call('migrate', ['--force' => true]);

        $this->call('db:seed', ['--force' => true]);

        /*
         * Migrate data structure
         */
        $this->info('Migrate database configuration');
        $collect = [];
        PackageManager::withInstalledPackages(function ($name, $info) use (&$collect) {
            $collect['Migrate ' . $name] = fn () => $this->callSilent('package', [
                'package'   => $name,
                '--migrate' => true,
            ]) == 0;
        });
        collect($collect)->each(fn ($task, $description) => $this->components->task($description, $task));
        Artisan::call('optimize:clear');

        /*
         * Sync package information
         */
        $this->info('Sync package information');
        $collect = [];
        PackageManager::withInstalledPackages(function ($name) use (&$collect) {
            $collect['Sync ' . $name] = fn () => $this->callSilent('package', [
                'package' => $name,
                '--sync'  => true,
            ]) == 0;
        });
        collect($collect)->each(fn ($task, $description) => $this->components->task($description, $task));
        Artisan::call('optimize:clear');

        /*
         * Sync package information
         */
        $this->info('Sync package information');
        $collect = [];
        PackageManager::withInstalledPackages(function ($name) use (&$collect) {
            $collect['Seeding ' . $name] = fn () => $this->callSilent('package', [
                'package' => $name,
                '--seed'  => true,
            ]) == 0;
        });
        collect($collect)->each(fn ($task, $description) => $this->components->task($description, $task));
        Artisan::call('optimize:clear');

        /*
         * Sync package information
         */
        $this->info('Integration package information');
        $collect = [];
        PackageManager::withInstalledPackages(function ($name) use (&$collect) {
            $collect['Setup ' . $name] = fn () => $this->callSilent('package', [
                'package'    => $name,
                '--dispatch' => true,
            ]) == 0;
        });
        collect($collect)->each(fn ($task, $description) => $this->components->task($description, $task));
        Artisan::call('optimize:clear');

        return 0;
    }
}
