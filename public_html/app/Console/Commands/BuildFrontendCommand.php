<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MetaFox\Layout\Jobs\CheckBuild;
use MetaFox\Layout\Jobs\CreateBuild;
use Symfony\Component\Console\Input\InputOption;

class BuildFrontendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'frontend:build';

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
        $reason = $this->option('reason');

        if ($this->option('check')) {
            CheckBuild::dispatchSync();
        } else {
            CreateBuild::dispatchSync($reason ?? __p('layout::phrase.rebuild_site_action'));
        }

        return 0;
    }

    public function getOptions()
    {
        return [
            ['check', null, InputOption::VALUE_NONE, 'Run Check'],
            ['reason', null, InputOption::VALUE_OPTIONAL, 'Rebuild site'],
        ];
    }
}
