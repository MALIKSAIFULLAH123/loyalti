<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use MetaFox\Platform\Console\CodeGeneratorTrait;

class MakeModelCommand extends Command
{
    use CodeGeneratorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:make-model {package}
    {--name= : name of model}
    {--table=table : add repository skeleton}
    {--entity=entity : entity type}
    {--has-repository : add repository skeleton}
    {--ver=v1 : versioning of api resource}
    {--content : main model is content}
    {--has-factory : generate model factory}
    {--has-category : generate model categories}
    {--overwrite : overwrite existing file}
    {--has-tag : generate model tags}
    {--has-policy : generate model policy}
    {--has-text : model has external text data}
    {--has-privacy : Generate privacy}
    {--has-observer : Generate observer}
    {--dry : Dry run}
    {--test : generate test class skeleton}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make model command';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $config = [];
        $version = $this->option('ver');
        $name = $this->option('name');
        $table = $this->option('table');
        $entityType = $this->option('entity');

        $studlyName = Str::studly($name);

        $this->ensureEloquentModel($name, $config);

        $this->ensureEloquentModelTest($name, $config);

        if ($this->option('has-text')) {
            $this->createModelText($studlyName, $table, $entityType);
        }

        if ($this->option('has-category')) {
            $this->ensureCategoryData($studlyName, $table, $entityType);
        }

        if ($this->option('has-tag')) {
            $this->createModelTagData($studlyName, $table, $entityType);
        }

        if ($this->option('has-privacy')) {
            $this->createModelPrivacyStream($studlyName, $table, $entityType);

            $this->createModelNetworkStream($studlyName, $table, $entityType);
        }

        if ($this->option('has-observer')) {
            $this->ensureEloquentModelObserver($name, $config);
        }

        if ($this->option('has-repository')) {
            $this->ensureRepository($name, $config);
        }

        if ($this->option('has-policy')) {
            $this->ensureEloquentModelPolicy($name, $config);
        }

        if ($this->option('has-factory')) {
            $this->ensureEloquentModelFactory($name, $config);
        }

        return 0;
    }
}
