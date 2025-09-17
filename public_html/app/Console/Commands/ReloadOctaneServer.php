<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Octane\Swoole\ServerProcessInspector as SwooleServerProcessInspector;

class ReloadOctaneServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'octane:reload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reload Octane Server';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->reloadSwooleServer();

        return Command::SUCCESS;
    }

    /**
     * Reload the Swoole server for Octane.
     *
     * @return int
     */
    protected function reloadSwooleServer()
    {
        $inspector = app(SwooleServerProcessInspector::class);

        if (!$inspector->serverIsRunning()) {
            $this->error('Octane server is not running.');

            return 1;
        }

        $this->info('Reloading workers...');

        $inspector->reloadServer();

        return 0;
    }
}
