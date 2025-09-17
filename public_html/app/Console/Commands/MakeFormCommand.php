<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use MetaFox\Platform\Console\CodeGeneratorTrait;

class MakeFormCommand extends Command
{
    use CodeGeneratorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:make-form {package}
    {--ver= : Version name. Example: v1}
    {--request : Make associate request class}
    {--action= : Model or directory name}
    {--name= : Model or directory name}
    {--admin : Is admincp namespace}
    {--dry : Dry run}
    {--test : Generate test class ?}
    {--overwrite : Overwrite existing files?}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make package form command';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $version = $this->option('ver');
        $name = (string) $this->option('name');
        $action = (string) $this->option('action');
        $this->generateForm(
            $this->option('admin'),
            $name,
            $action,
            $version,
            []
        );
        $this->generateFormRequest(
            $this->option('admin'),
            $this->option('name'),
            $this->option('action'),
            $this->option('ver')
        );

        return 0;
    }
}
