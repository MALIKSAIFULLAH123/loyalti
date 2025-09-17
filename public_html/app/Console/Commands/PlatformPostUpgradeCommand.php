<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MetaFox\App\Jobs\VerifyMetaFoxInfo;
use MetaFox\Core\Jobs\UpdateAdminSearch;
use MetaFox\Core\Jobs\UpdateSiteStatistic;
use MetaFox\Core\Models\StatsContent;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;

class PlatformPostUpgradeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metafox:postupgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run after platform upgraded';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        UpdateSiteStatistic::dispatchSync();
        Artisan::call('queue:restart');
        UpdateSiteStatistic::dispatchSync(StatsContent::STAT_PERIOD_ONE_HOUR);

        Settings::save([
            'core.platform.upgraded_at'    => Carbon::now()->toIso8601String(),
            'core.platform.latest_version' => MetaFox::getVersion(),
        ]);

        $this->updateMetaFoxVersion();
        $this->reloadWebSettings();
        VerifyMetaFoxInfo::dispatchSync();

        app('events')->dispatch('metafox:upgraded');

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

    private function reloadWebSettings()
    {
        if (app()->isLocal()) {
            return;
        }

        try {
            $url      = url_utility()->makeApiFullUrl('api/v1/core/web/settings/now');
            $response = Http::get($url);

            return $response->body();
        } catch (Exception $e) {
            Log::channel('installation')->error('reload web settings', [$url, $e->getMessage()]);

            return null;
        }
    }
}
