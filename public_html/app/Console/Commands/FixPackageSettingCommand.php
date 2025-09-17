<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Console\CodeGeneratorTrait;
use MetaFox\Platform\PackageManager;

class FixPackageSettingCommand extends Command
{
    use CodeGeneratorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:fix {package}
    {--vendor= : Vendor Name}
    {--name= : Application Name}
    {--homepage= : Author homepage}
    {--author= : Author name}
    {--dry : Dry run}
    {--test : Generate test sample code?}
    {--overwrite : Overwrite current}';

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
    public function handle(): int
    {
        $package = $this->argument('package');

        if ($package === 'all') {
            foreach (PackageManager::getPackageNames() as $packageName) {
                Artisan::call('package:fix', ['package'=>$packageName]);
            }
        } else {
            $this->fix();
        }

        return 0;
    }

    public function fix(): void
    {
        $this->translate(
            'src/Http/Resources/v1/PackageSetting.php',
            'src/Http/v1/PackageSetting',
            $this->getReplacements()
        );

        $this->fixPackageSettingDriver();
    }

    public function fixPackageSettingDriver(): void
    {
        $packageName = $this->getPackageName();
        $namespace = $this->getPackageNamespace();
        $name = $this->getPackageAlias();
        $filename = 'resources/drivers.php';

        $drivers = PackageManager::readFile($packageName, $filename);

        $exist = Arr::first($drivers, function ($item) {
            return $item['type'] === 'package-setting';
        });

        if ($exist) {
            return;
        }

        $drivers[] = [
            'driver'  => $namespace . '\Http\Resources\v1\PackageSetting',
            'type'    => 'package-setting',
            'name'    => $name,
            'version' => 'v1',
        ];

        PackageManager::exportToFilesystem($packageName, $filename, $drivers);
    }
}
