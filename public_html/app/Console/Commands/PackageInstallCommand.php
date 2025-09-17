<?php

namespace App\Console\Commands;

use App\ProcessHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use MetaFox\Platform\PackageManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PackageInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'package:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install uploaded package';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id   = $this->argument('package');
        $path = PackageManager::getPath($id);
        $processHelper = ProcessHelper::factory();

        // skip compiled, install depencies, optimize etc....
        $fast  = $this->option('fast');

        if (!$fast) {
            $this->call('clear-compiled');
            $processHelper->installAllDependencies(false);
            $processHelper->verifyComposerInstalled();
        }

        if (!$path || !is_dir($path)) {
            $this->error('Failed finding package ' . $id . '. Run `artisan package:discover` to find package again!');
        }

        $this->call('package:discover');
        $json = PackageManager::getComposerJson($id);
        $peerDependencies = Arr::get($json, 'extra.metafox.peerDependencies');

        if(!empty($peerDependencies) && is_array($peerDependencies)){
            foreach($peerDependencies as $dependency){
                $this->info("Install peer dependency package $dependency");
                $this->call('package:install', [
                    'package'=> $dependency,
                    '--fast' => true,
                ]);
            };
        }

        $processHelper->callPackage($id, $this->option('refresh') ? '--migrate-refresh' : '--migrate');

        $processHelper->callPackage($id, '--sync');

        $processHelper->callPackage($id, '--seed');

        $processHelper->callPackage($id, '--dispatch');

        $processHelper->callPackage($id, '--installed');

        if(!$fast){
            $processHelper->callPhp('artisan optimize');
        }

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
            ['refresh', null, InputOption::VALUE_NONE, 'Reset migration'],
            ['fast', null, InputOption::VALUE_NONE, 'disable composer install'],
        ];
    }
}
