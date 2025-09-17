<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Platform\PackageManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PackageUninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'package:uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall a specific package';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id      = $this->argument('package');
        $package = resolve(PackageRepositoryInterface::class)->findByName($id);

        if (!$package) {
            $this->info(sprintf('Package %s not found.', $id));

            return 0;
        }

        $package->is_active = 0;
        $package->saveQuietly();

        Log::channel('installation')->error('deleting package', [$id]);

        app('events')->dispatch('packages.deleting', [$id]);

        $path = PackageManager::getPath($id);
        Log::channel('installation')->error('deleted package', [$id]);

        app('events')->dispatch('packages.deleted', [$id]);

        $package->is_installed = 0;
        $package->saveQuietly();

        // Delete app.
        // please ensure delete path
        if ($this->option('clean') && $path) {
            $realpath = base_path($path);
            if (str_starts_with($realpath, base_path('packages'))) {
                File::deleteDirectory($realpath);
            }

            $this->info(sprintf('Delete package source at %s is uninstalled.', $path));
            $package->forceDelete();
            $this->call('package:discover');
        }

        Artisan::call('optimize:clear');

        $this->info(sprintf('Package %s is uninstalled.', $id));

        return 0;
    }

    public function getOptions()
    {
        return [
            ['clean', null, InputOption::VALUE_NONE, 'Cleanup source code and others.'],
        ];
    }

    protected function getArguments()
    {
        return [
            ['package', InputArgument::REQUIRED, 'Package name'],
        ];
    }
}
