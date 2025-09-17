<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Jobs\UpdateAdminSearch;
use MetaFox\Core\Jobs\UpdateSiteStatistic;
use MetaFox\Core\Models\StatsContent;

class PackagePostInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:postinstall';

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
        $id = getenv('MFOX_PACKAGE');

        Log::channel('installation')->debug("invoke ".__METHOD__);

        UpdateAdminSearch::dispatchSync();
        UpdateSiteStatistic::dispatchSync();
        UpdateSiteStatistic::dispatchSync(StatsContent::STAT_PERIOD_ONE_HOUR);

        return Command::SUCCESS;
    }
}
