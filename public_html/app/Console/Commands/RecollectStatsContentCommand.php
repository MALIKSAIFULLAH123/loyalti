<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use MetaFox\Core\Repositories\StatsContentRepositoryInterface;

class RecollectStatsContentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metafox:recollect-stats
    {--group= : This specifies the group which data should be collected}
    {--days= : This specifies the data range}
    {--fresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recollect site statistics';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $days   = $this->option('days');
        $group  = $this->option('group');
        $fresh  = $this->option('fresh');
        $carbon = Carbon::now()->startOfHour();
        if ($days === null) {
            $days = 365;

            if ($fresh) {
                $this->info('Delete old statistical data by group.');
                $this->cleanupOldStatsByGroup($group ?? '*');
            }
        }

        $this->info('Recollection of site statistics started....');

        $this->repository()->recoverHourStat($group, $carbon, $days);

        $this->info('Recollection of site statistics completed.');

        return 0;
    }

    public function repository(): StatsContentRepositoryInterface
    {
        return resolve(StatsContentRepositoryInterface::class);
    }

    /**
     * Delete old statistical data by group when the number of days exceeds the limit
     *
     * @param string $group
     * @return int
     */
    public function cleanupOldStatsByGroup(string $group): int
    {
        $query = $this->repository()->getModel()->newModelQuery();
        $query->where('group', $group);

        return $query->delete();
    }
}
