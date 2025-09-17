<?php

namespace App\Console\Commands;

use Composer\Console\Input\InputOption;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\PackageManager;

class HealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'metafox:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute health check';

    protected array $reports = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $checkers = Arr::flatten(PackageManager::discoverSettings('getCheckers'));

        $list = $this->option('list');

        if ($list) {
            foreach ($checkers as $className) {
                /** @var Checker $checker */
                $checker = resolve($className);
                $this->components->twoColumnDetail(
                    $checker::class,
                    sprintf('<info>%s</info>', $checker->getName())
                );
            }

            return 0;
        }

        $collect = [];

        foreach ($checkers as $className) {
            try {
                /** @var Checker $checker */
                $checker = resolve($className);

                $collect[$checker->getName()] = fn () => $this->check($checker) == static::SUCCESS;
            } catch (\Exception $exception) {
                $this->error($exception->getMessage());
            }
        }

        collect($collect)->each(fn ($task, $description) => $this->components->task($description, $task));

        if ($this->option('verbose')) {
            $this->info('Generate Reports ..........');

            $reports = Arr::flatten($this->reports, 1);

            foreach ($reports as $report) {
                $detail = $report['severity'];
                switch ($detail) {
                    case 'error':
                        $detail = '<error>' . $detail . '</error>';
                        break;
                    case 'success':
                        $detail = '<info>' . $detail . '</info>';
                        break;
                    case 'warn':
                        $detail = '<comment>' . $detail . '</comment>';
                }

                $this->components->twoColumnDetail($report['message'], $detail);
            }
        }

        return Command::SUCCESS;
    }

    public function check(Checker $checker)
    {
        $result = $checker->check();

        $this->reports[] = $result->getReports();

        return $result->okay() ? Command::SUCCESS : Command::FAILURE;
    }

    protected function getOptions()
    {
        return [
            ['list', null, InputOption::VALUE_NONE, 'Listing support checker'],
        ];
    }
}
