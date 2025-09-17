<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MetaFox\Platform\Console\CodeGeneratorTrait;
use Symfony\Component\Console\Input\InputOption;

class MakeMigrationCommand extends Command
{
    use CodeGeneratorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'package:make-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make database migration within package.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $name = $this->option('name');

        $migration = sprintf('%s_migrate_%s_table.php', date('Y_m_d_HIS'), $name);

        $this->translate(
            'src/Database/Migrations/' . $migration,
            '/packages/database/migration.stub',
            [
                'NAME'  => \Str::studly($name),
                'TABLE' => $name,
            ]
        );

        $this->info('A new migration file is created in the directory "src/Database/Migrations"
For more information, visit https://dev-docs.metafoxapp.com/backend/eloquent#migrations');

        return 0;
    }

    /**
     * @return array[]
     * @link \MetaFox\Core\Http\Requests\v1\Code\Admin\MakeSeederRequest::rules()
     */
    protected function getOptions()
    {
        return [
            ['name', null, InputOption::VALUE_REQUIRED, 'Migration type name', null],
            ['overwrite', null, InputOption::VALUE_OPTIONAL, 'Overwrite existing files?.', false],
            ['test', null, InputOption::VALUE_OPTIONAL, 'Generate test class?.', false],
            ['test', null, InputOption::VALUE_OPTIONAL, 'Generate test class?.', false],
            ['dry', null, InputOption::VALUE_OPTIONAL, 'Dry run test class?.', false],
            ['ver', null, InputOption::VALUE_OPTIONAL, 'Version?.', false],
        ];
    }
}
