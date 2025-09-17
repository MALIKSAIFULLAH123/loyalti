<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use MetaFox\Platform\Console\CodeGeneratorTrait;

class MakeApiControllerCommand extends Command
{
    use CodeGeneratorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:make-api-controller {package}
    {--ver= : Version name. Example: v1}
    {--name= : Model or directory name}
    {--dry : Dry run?}
    {--admin : Is admincp namespace}
    {--test : Generate test class ?}
    {--overwrite : Overwrite existing files?}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make package API controller';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $version = $this->option('ver');
        $config = [];
        $name = (string) $this->option('name');
        $package = (string) $this->argument('package');
        $admincp = $this->option('admin');

        if (!$admincp) {
            $this->generateResourceSettings($admincp, $name, 'mobile', $version, $config);
        }

        $this->generateResourceSettings($admincp, $name, 'web', $version, $config);

        $this->ensureResourceApiGateway($admincp, $name, $version, $config);

        $this->generateResourceVariant($admincp, $name, 'item', $version, $config);
        $this->generateResourceVariant($admincp, $name, 'detail', $version, $config);

        $this->generateFormRequest($admincp, $name, 'index', $version, $config);
        $this->generateFormRequest($admincp, $name, 'update', $version, $config);
        $this->generateFormRequest($admincp, $name, 'store', $version, $config);

        $this->info('To create skeleton for frontend.
1. Open Terminal.
2. Go to the root of the frontend project.
3. Run command: yarn metafox create-resource ' . $package . ' ' . Str::kebab($name) .
' ');

        return 0;
    }
}
