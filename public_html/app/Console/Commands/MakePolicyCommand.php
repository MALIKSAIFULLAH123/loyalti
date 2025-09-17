<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use MetaFox\Platform\Console\CodeGeneratorTrait;
use Symfony\Component\Console\Input\InputOption;

class MakePolicyCommand extends Command
{
    use CodeGeneratorTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'package:make-policy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make policy handler class';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $NAME = \Str::studly($this->option('name'));

        if (!Str::endsWith($NAME, 'Policy')) {
            $NAME = Str::replace('Policy', '', $NAME);
        }

        $this->translate(
            'src/Policies/$NAME$Policy.php',
            'packages/policies/model_policy.stub',
            $this->getReplacements(['NAME' => $NAME])
        );

        $this->translate(
            'test/Unit/Policies/$NAME$PolicyTest.php',
            'packages/policies/model_policy_test.stub',
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
            ['entity', null, InputOption::VALUE_REQUIRED, 'Entity name', null],
            ['overwrite', null, InputOption::VALUE_OPTIONAL, 'Overwrite existing files?.', false],
            ['test', null, InputOption::VALUE_OPTIONAL, 'Generate test class?.', false],
            ['test', null, InputOption::VALUE_OPTIONAL, 'Generate test class?.', false],
            ['dry', null, InputOption::VALUE_OPTIONAL, 'Dry run test class?.', false],
            ['ver', null, InputOption::VALUE_OPTIONAL, 'Version?.', false],
        ];
    }
}
