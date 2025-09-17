<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PackageDeactivateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'package:deactivate {package}
    {--rebuild}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate installed package';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id   = $this->argument('package');
        $shouldRebuild  = $this->option('rebuild');

        $package = $this->packageRepository()->findByName($id);
        if (!$package) {
            $this->error(sprintf('The designated package %s could not be found. Please install the package first.', $id));
            return;
        }

        if (!$package->is_active) {
            $this->comment(sprintf('Package %s has already been deactivated.', $id));
            return;
        }

        $package->update(['is_active' => 0]);

        $this->call('optimize:clear');

        if ($shouldRebuild) {
            $this->call('frontend:build');
            $this->info('Build request sent');
        }

        $this->info(sprintf('Package %s has been successfully deactivated.', $id));

        return 0;
    }

    /**
     * @return array[]
     */
    public function getArguments()
    {
        return [
            ['package', InputArgument::REQUIRED, 'Package name etc: metafox/core'],
        ];
    }

    /**
     * @return array[]
     */
    public function getOptions()
    {
        return [
            ['rebuild', null, InputOption::VALUE_NONE, 'Should rebuild frontend after activating package.'],
        ];
    }

    private function packageRepository(): PackageRepositoryInterface
    {
        return resolve(PackageRepositoryInterface::class);
    }
}
