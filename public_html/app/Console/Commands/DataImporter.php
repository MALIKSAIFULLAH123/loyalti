<?php

namespace App\Console\Commands;

use Composer\Console\Input\InputOption;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use MetaFox\ChatPlus\Repositories\ChatServerInterface;
use MetaFox\Importer\Jobs\ImportMonitor;
use MetaFox\Importer\Models\Bundle;
use MetaFox\Importer\Repositories\BundleRepositoryInterface;

class DataImporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'data:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $restart     = (bool) $this->option('restart');
        $filter      = $this->option('filter');
        $chatType    = $this->option('chatType');
        $pushChatJob = $this->option('pushChatJob');
        $wipe        = (bool) $this->option('wipe');

        if ($pushChatJob && app_active('metafox/chatplus')) {
            $this->comment('Export migrated users to Chatplus server');
            $resource = $this->option('pushChatJobResource') ?? 'user';
            $source   = $this->option('pushChatJobSource') ?? 'phpfox';
            resolve(ChatServerInterface::class)->importUsers($resource, $source);

            return 0;
        }
        if ($this->option('continue')) { // only continue.
            Artisan::call('queue:prune-failed');
            $this->comment('Import continue');
            ImportMonitor::dispatch();

            return 0;
        }

        if ($filter) {
            $filter = explode(',', str_replace('#\S+#', '', $filter));
        }

        if ($wipe) {
            DB::table('importer_entries')->truncate();
            $this->info('Truncated table importer_entries.');
            DB::table('importer_bundle')->truncate();
            $this->info('Truncated table importer_bundle.');
            DB::table('importer_logs')->truncate();
            $this->info('Truncated table importer_logs.');
            DB::table('importer_ids')->truncate();
            $this->info('Truncated table importer_ids.');
        }

        if ($restart) {
            DB::table('importer_entries')->update(['status' => 'initial']);
            $this->info('Reset importer_entries.status="initial"');
            DB::table('importer_bundle')->update(['status' => 'initial']);
            $this->info('Reset importer_bundle.status="initial"');
            DB::table('importer_logs')->truncate();
            $this->info('Truncated table importer_logs.');
            Artisan::call('queue:flush');
        }

        if ($filter && count($filter)) {
            // update bundle
            Bundle::query()->whereIn('resource', $filter)->update([
                'status' => 'initial',
            ]);
        }

        // Reset all.
        $this->info('Scan storage/app/importer/schedule.json for bundling.');

        $bundleRepository = resolve(BundleRepositoryInterface::class);
        $filename         = 'storage/app/importer/schedule.json';
        if (in_array($chatType, ['chat', 'chatplus'])) {
            $bundleRepository->selectChatApp($chatType);
        }
        $bundleRepository->importScheduleJson($filename, $filter);
        $bundleRepository->addLockFile();

        ImportMonitor::dispatch();

        return 0;
    }

    protected function getOptions()
    {
        return [
            ['restart', null, InputOption::VALUE_NONE, 'Restart importer queue'],
            ['continue', null, InputOption::VALUE_NONE, 'Continue'],
            ['filter', null, InputOption::VALUE_OPTIONAL, 'Filter for resource'],
            ['wipe', null, InputOption::VALUE_OPTIONAL, 'Truncate importer_* tables'],
            ['chatType', null, InputOption::VALUE_OPTIONAL, 'Select Chat app to import'],
            ['pushChatJob', null, InputOption::VALUE_NONE, 'Export migrated users to Chatplus server'],
            ['pushChatJobResource', null, InputOption::VALUE_OPTIONAL, 'User resource name'],
            ['pushChatJobSource', null, InputOption::VALUE_OPTIONAL, 'Source name'],
        ];
    }
}
