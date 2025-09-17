<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MetaFox\Platform\Checksum;
use Symfony\Component\Console\Input\InputOption;

class ChecksumCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'checksum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test, Generate Checksum';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $basePath = $this->option("base");
        if(!$basePath){
            $basePath = base_path("/");
        }

        if($this->option('check')){
            $files = Checksum::testChecksum();
            $this->table(array('file', 'status'), array_map(function($item){
                return [$item['name'], $item['status']];
            }, $files));
        }


        if($this->option('generate')){
            $files  = Checksum::generatePlatformChecksum($basePath);
            $this->table(array('file', 'size'), array_map(function($item){
                return [$item,  human_readable_bytes(filesize(base_path($item)))];
            }, $files));
            $this->info("Generated package checksum files");
        }
    }


    public function getOptions()
    {
        return [
            ['check', null, InputOption::VALUE_NONE, 'Run Test Checksum'],
            ['generate', null, InputOption::VALUE_NONE, 'Generate checksum'],
            ['base', null, InputOption::VALUE_OPTIONAL, 'Base Path'],
        ];
    }
}
