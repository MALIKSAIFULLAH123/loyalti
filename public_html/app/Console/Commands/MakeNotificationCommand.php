<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use MetaFox\Platform\Console\CodeGeneratorTrait;
use Symfony\Component\Console\Input\InputOption;

class MakeNotificationCommand extends Command
{
    use CodeGeneratorTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'package:make-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make notification handler class';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $NAME = \Str::studly($this->option('name'));

        $this->translate(
            'src/Notifications/$NAME$.php',
            'packages/notifications/notification.stub',
            $this->getReplacements(['NAME' => $NAME])
        );

        return 0;
    }

    protected function getOptions()
    {
        return [
            ['name', null, InputOption::VALUE_REQUIRED, 'Seeder class name', null],
            ['overwrite', null, InputOption::VALUE_OPTIONAL, 'Overwrite existing files?.', false],
            ['test', null, InputOption::VALUE_OPTIONAL, 'Generate test class?.', false],
            ['test', null, InputOption::VALUE_OPTIONAL, 'Generate test class?.', false],
            ['dry', null, InputOption::VALUE_OPTIONAL, 'Dry run test class?.', false],
            ['ver', null, InputOption::VALUE_OPTIONAL, 'Version?.', false],
        ];
    }
}
