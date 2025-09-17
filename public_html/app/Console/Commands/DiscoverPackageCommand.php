<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Platform\PackageManager;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class DiscoverPackageCommand.
 */
class DiscoverPackageCommand extends Command
{
    /**
     * The name of package.
     *
     * @var string
     */
    protected $name = 'package:discover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bundle Packages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (!function_exists('discover_metafox_packages')) {
            $this->error('missing function discover_metafox_packages()');

            return 1;
        }

        $packages = discover_metafox_packages(base_path(), true);

        config()->set([
            'metafox.packages' => $packages,
        ]);

        return 0;
    }

    public function getOptions()
    {
        return [
            ['up', null, InputOption::VALUE_NONE, 'Up to package repository'],
        ];
    }
}
