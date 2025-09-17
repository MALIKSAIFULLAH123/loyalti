<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MetaFox\Platform\Console\CodeGeneratorTrait;
use Symfony\Component\Console\Input\InputOption;

class MakeCategoryCommand extends Command
{
    use CodeGeneratorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'package:make-category';

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
        $name = $this->option('name');
        $studlyName = \Str::studly($name);
        $entityType = $this->option('table');

        $this->translate(
            "src/Models/{$studlyName}.php",
            '/packages/models/model.stub',
            [
                'NAME'              => $studlyName,
                'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                'PACKAGE_ALIAS'     => $this->getPackageAlias(),
                'TABLE'             => $entityType,
                'ENTITY_TYPE'       => $entityType,
            ],
        );
        $this->ensureRepository($studlyName, []);
        $this->ensureEloquentModelFactory($studlyName);

        // for admincp
        $this->ensureResourceApiGateway(true, $studlyName, 'v1', []);
        $this->generateFormRequest(true, $studlyName, 'index', 'v1', []);
        $this->generateFormRequest(true, $studlyName, 'update', 'v1', []);
        $this->generateFormRequest(true, $studlyName, 'store', 'v1', []);

        $this->generateForm(true, $studlyName, 'destroy', 'v1', []);
        $this->generateForm(true, $studlyName, 'store', 'v1', []);
        $this->generateForm(true, $studlyName, 'update', 'v1', []);

        $this->generateResourceVariant(true, $studlyName, 'item', 'v1');
        $this->generateResourceVariant(true, $studlyName, 'item', 'v1');
        $this->generateResourceVariant(true, $studlyName, 'detail', 'v1');
        $this->generateResourceVariant(true, $studlyName, 'detail', 'v1');

        // for web
        $this->ensureResourceApiGateway(false, $studlyName, 'v1', []);
        $this->generateResourceSettings(true, $studlyName, 'web', 'v1');
        $this->generateResourceSettings(false, $studlyName, 'mobile', 'v1');

        $this->generateFormRequest(false, $studlyName, 'index', 'v1', []);

        return 0;
    }

    /**
     * @return array[]
     * @link \MetaFox\Core\Http\Requests\v1\Code\Admin\MakeSeederRequest::rules()
     */
    protected function getOptions()
    {
        return [
            ['name', null, InputOption::VALUE_REQUIRED, 'Seeder class name', null],
            ['overwrite', null, InputOption::VALUE_OPTIONAL, 'Overwrite existing files?', false],
            ['table', null, InputOption::VALUE_OPTIONAL, 'Indicates table name?', false],
            ['entity', null, InputOption::VALUE_OPTIONAL, 'Indicates entity name?', false],
            ['test', null, InputOption::VALUE_OPTIONAL, 'Generate test class?.', false],
            ['dry', null, InputOption::VALUE_OPTIONAL, 'Dry run test class?.', false],
            ['ver', null, InputOption::VALUE_OPTIONAL, 'Version?.', false],
        ];
    }
}
