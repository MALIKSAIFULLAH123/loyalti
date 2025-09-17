<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MetaFox\Platform\Console\CodeGeneratorTrait;
use Symfony\Component\Console\Input\InputOption;

class MakeJobCommand extends Command
{
    use CodeGeneratorTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'package:make-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make job class.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $NAME = \Str::studly($this->option('name'));

        $stub = $this->option('sync') ? 'packages/jobs/job.stub' :
            'packages/jobs/job-queued.stub';

        $this->translate(
            'src/Jobs/$NAME$.php',
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
            ['overwrite', null, InputOption::VALUE_OPTIONAL, 'Overwrite existing files?.', false],
            ['test', null, InputOption::VALUE_OPTIONAL, 'Generate an accompanying PHPUnit test for the?.', false],
            ['sync', null, InputOption::VALUE_OPTIONAL, 'Indicates that job should be synchronous?.', false],
            ['dry', null, InputOption::VALUE_OPTIONAL, 'Dry run test class?.', false],
            ['ver', null, InputOption::VALUE_OPTIONAL, 'Version?.', false],
        ];
    }
}
