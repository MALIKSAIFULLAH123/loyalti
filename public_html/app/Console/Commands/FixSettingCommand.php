<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FixSettingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'fix:setting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update form structure';

    public function handle(): int
    {
        $path = $this->argument('path');
        $path = app('files')->exists($path) ? $path : base_path($path);

        $this->fixFormat($path);

        $content = app('files')->get($path);
        $content = $this->migrateConfig($content);
//        $content = $this->migrateBuilder($content);

        $this->writeToFile($path, $content);

        return 0;
    }

    private function fixFormat($filename): void
    {
        $content = app('files')->get($filename);

        $content = preg_replace('/\(\s{13}\[/m', '([', $content);
        $content = preg_replace('/]\s{9}\);/m', ']);', $content);
        $content = preg_replace('/=>(\s*)\[(.+)$/m', '=>$1[' . PHP_EOL . '$2', $content);
        $content = preg_replace('/\'],$/m', '\'' . PHP_EOL . '],', $content);

        app('files')->put($filename, $content);

        exec('composer phpcs ' . $filename);
    }

    public function migrateConfig(string $content): string
    {
        $content = preg_replace('/^\s{12}\'(\w+)\'\s+=>\s+\[/m', '$this->add(\'$1\')', $content);
        $content = preg_replace('/\s{20,}/m', '', $content);
        $content = preg_replace('/\s{16,}\],?/m', ']', $content);
        $content = preg_replace('/^\s{16}\'(\w+)\'\s+=>\s+(.+),?$/m', '->$1($2)', $content);
        $content = preg_replace('/,\)/m', ')', $content);
        $content = preg_replace('/^\s{12}\],?/m', ';' . PHP_EOL, $content);
        $content = preg_replace('/^\s{8}\]\);?/m', '', $content);
        $content = preg_replace('/^\s{8}\$this->addActions\(\[/m', '', $content);
        $content = preg_replace('/(apiUrl|pageUrl)\(\'\//m', '$1(\'', $content);
        $content = preg_replace('/@SuppressWarnings([.]+)$/m', '', $content);

        return $content;
    }

    /**
     * @param  string $path
     * @param  string $content
     * @return void
     */
    public function writeToFile(string $path, string $content): void
    {
        $filename = $this->option('test') ? $path . '.local.php' : $path;
        $files = app('files');
        $files->put($filename, $content);
        exec('composer phpcs ' . $filename);
    }

    public function getOptions()
    {
        return [
            ['test', 't', InputOption::VALUE_OPTIONAL, 'Test', null],
        ];
    }

    protected function getArguments()
    {
        return [
            ['path', null, InputArgument::REQUIRED, 'Path to file'],
        ];
    }
}
