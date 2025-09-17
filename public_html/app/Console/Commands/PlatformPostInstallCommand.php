<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use MetaFox\App\Jobs\VerifyMetaFoxInfo;
use MetaFox\Core\Jobs\UpdateAdminSearch;
use MetaFox\Core\Jobs\UpdateSiteStatistic;
use MetaFox\Core\Models\StatsContent;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;

class PlatformPostInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metafox:postinstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run after platform installed';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        UpdateSiteStatistic::dispatchSync();
        UpdateSiteStatistic::dispatchSync(StatsContent::STAT_PERIOD_ONE_HOUR);

        Settings::save([
            'core.platform.installed_at'   => Carbon::now()->toIso8601String(),
            'core.platform.upgraded_at'    => Carbon::now()->toIso8601String(),
            'core.platform.latest_version' => MetaFox::getVersion(),
        ]);

        $this->updateMetaFoxVersion();

        Log::channel('installation')->debug(__METHOD__ . ' sucessfully.');

        app('events')->dispatch('metafox:installed');

        return 0;
    }

    /**
     * Write a new environment file with the given key.
     *
     * @return void
     */
    protected function updateMetaFoxVersion()
    {
        $filename = $this->laravel->environmentFilePath();
        $content  = mf_get_contents($this->laravel->environmentFilePath());
        $pattern  = '/^MFOX_APP_VERSION=(.*)$/m';
        $need     = 'MFOX_APP_VERSION=' . MetaFoxConstant::VERSION;

        if (preg_match($pattern, $content)) {
            $content = preg_replace(
                $pattern,
                $need,
                $content
            );
        } else {
            $content = $content . PHP_EOL . $need . PHP_EOL;
        }

        file_put_contents($filename, $content);
    }
}
