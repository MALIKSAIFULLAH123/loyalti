<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Core\Jobs\UpdateAdminSearch;
use MetaFox\Core\Jobs\UpdateSiteStatistic;
use MetaFox\Core\Models\StatsContent;
use MetaFox\Platform\PackageManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PackageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'package';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('package');
        $path = PackageManager::getPath($id);

        if (!$path || !is_dir($path)) {
            $this->error('Failed finding package '.$id.'. Run `artisan package:discover` to find package again!');
        }

        if ($this->option('migrate')) {
            Log::channel('installation')->debug(
                sprintf("RUN artisan migrate --path %s --force", PackageManager::getMigrationPath($id)),
            );
            $this->call('migrate', [
                '--path'  => PackageManager::getMigrationPath($id),
                '--force' => true,
            ]);
        }

        if ($this->option('migrate-refresh')) {
            Log::channel('installation')->debug(
                sprintf("RUN artisan migrate:refresh --path %s --force", PackageManager::getMigrationPath($id)),
            );
            $this->call('migrate:refresh', [
                '--path'  => PackageManager::getMigrationPath($id),
                '--force' => true,
            ]);
        }

        if ($this->option('migrate-rollback')) {
            Log::channel('installation')->debug(
                sprintf("RUN artisan migrate:rollback --path %s --force", PackageManager::getMigrationPath($id)),
            );
            $this->call('migrate:rollback', [
                '--path'  => PackageManager::getMigrationPath($id),
                '--force' => true,
            ]);
        }

        if ($this->option('migrate-status')) {
            Log::channel('installation')->debug(
                sprintf("RUN artisan migrate:status --path %s", PackageManager::getMigrationPath($id)),
            );
            $this->call('migrate:status', [
                '--path' => PackageManager::getMigrationPath($id),
            ]);
        }

        if ($this->option('sync')) {
            $info = PackageManager::getComposerJson($id);

            $item = resolve(PackageRepositoryInterface::class)->syncComposerInfo($info);

            if (!$item->is_installed) {
                $item->is_installed = true;
                $item->is_active = true;
                $item->saveQuietly();
            }
        }

        if ($this->option('seed')) {
            $seederClass = PackageManager::getSeeder($id);
            if ($seederClass) {
                $this->call('db:seed', [
                    '--class' => $seederClass,
                    '--force' => true,
                ]);
            }
        }

        if ($this->option('dispatch')) {
            app('events')->dispatch('packages.installed', [$id]);
        }

        if($this->option('installed')){
            UpdateAdminSearch::dispatchSync();
            UpdateSiteStatistic::dispatchSync();
            UpdateSiteStatistic::dispatchSync(StatsContent::STAT_PERIOD_ONE_HOUR);
        }

        $this->call('optimize:clear');

        return 0;
    }

    public function getArguments()
    {
        return [
            ['package', InputArgument::REQUIRED, 'Package name etc: metafox/core'],
        ];
    }

    public function getOptions()
    {
        return [
            ['migrate', null, InputOption::VALUE_NONE, 'Run migrate'],
            ['migrate-refresh', null, InputOption::VALUE_NONE, 'Reset and re-run all migrations'],
            ['migrate-rollback', null, InputOption::VALUE_NONE, 'Rollback package migrations'],
            ['migrate-status', null, InputOption::VALUE_NONE, 'Check migrate status'],
            ['seed', null, InputOption::VALUE_NONE, 'Run seed'],
            ['force', null, InputOption::VALUE_NONE, 'Force run without confirmation'],
            ['dispatch', null, InputOption::VALUE_NONE, 'Run dispatch'],
            ['autoload', null, InputOption::VALUE_NONE, 'check autload'],
            ['sync', null, InputOption::VALUE_NONE, 'Run sync to package repository interface'],
            ['installed', null, InputOption::VALUE_NONE, 'Run sync to package and dispatch installed command'],
        ];
    }
}
