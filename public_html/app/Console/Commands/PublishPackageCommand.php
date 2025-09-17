<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MetaFox\App\Support\PackageExporter;
use Symfony\Component\Console\Input\InputOption;

class PublishPackageCommand extends Command
{
    protected $name = 'package:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bundle a package and publish to store';

    public function handle(): int
    {
        $packageName = $this->hasArgument('package') ? $this->argument('package') : null;
        $exporter = new PackageExporter();
        $packages = config('metafox.packages');
        $channel = $this->option('production') ? 'production' : 'development';
        $release = $this->option('release');

        if (!$packageName) {
            foreach ($packages as $packageName => $package) {
                if ($package['core'] ?? false) {
                    $this->comment('Skip bundling '.$packageName);
                    continue;
                }

                try {
                    $this->comment('Bundling '.$packageName);
                    $filename = $exporter->export($packageName, $release, $channel);
                    $this->info($filename);
                } catch (\Exception) {
                }
            }

            return 0;
        }

        $this->info(str_pad('', 60, '='));
        $this->info('Bundle package '.$packageName);

        $filename = $exporter->export($packageName, $release, $channel);

        $this->info($filename);

        return 0;
    }

    protected function getArguments(): array
    {
        return [
            ['package', null, InputOption::VALUE_OPTIONAL],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['production', null, InputOption::VALUE_NONE, 'Releaes to production channel'],
            ['release', null, InputOption::VALUE_NONE, 'Release to store'],
        ];
    }
}
