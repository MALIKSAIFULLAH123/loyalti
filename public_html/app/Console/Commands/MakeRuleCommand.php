<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use MetaFox\Platform\Console\CodeGeneratorTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MakeRuleCommand.
 * @link \MetaFox\Core\Http\Controllers\Api\v1\CodeAdminController::makeRule()
 */
class MakeRuleCommand extends Command
{
    use CodeGeneratorTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'package:make-rule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make rule class';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $NAME = \Str::studly($this->option('name'));

        if (!Str::endsWith($NAME, 'Rule')) {
            $NAME = $NAME . 'Rule';
        }

        $stub =  $this->option('implicit') ?
            'packages/rules/rule.stub' : 'packages/rules/implicit-rule.stub';

        $this->translate(
            'src/Rules/$NAME$Rule.php',
            $stub,
            $this->getReplacements(['NAME' => $NAME])
        );

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
            ['implicit', null, InputOption::VALUE_OPTIONAL, 'Generate an implicit rule?', false],
            ['test', null, InputOption::VALUE_OPTIONAL, 'Generate test class?.', false],
            ['test', null, InputOption::VALUE_OPTIONAL, 'Generate test class?.', false],
            ['dry', null, InputOption::VALUE_OPTIONAL, 'Dry run test class?.', false],
            ['ver', null, InputOption::VALUE_OPTIONAL, 'Version?.', false],
        ];
    }
}
