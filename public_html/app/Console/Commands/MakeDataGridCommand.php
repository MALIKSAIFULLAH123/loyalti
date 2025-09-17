<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use MetaFox\Core\Constants;
use MetaFox\Platform\Console\CodeGeneratorTrait;
use MetaFox\Platform\PackageManager;

/**
 * Class MakeDataGridCommand.
 * @ignore
 * @codeCoverageIgnore
 * @link    \MetaFox\Core\Http\Controllers\Api\v1\CodeAdminController::makeDataGrid
 */
class MakeDataGridCommand extends Command
{
    use CodeGeneratorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:make-datagrid {package}
    {--name= : Model Name}
    {--action= : Action name}
    {--ver= : Version Name}
    {--overwrite : Overwrite}
    {--dry : Dry run?}
    {--alias : Dry run?}
    {--admin : Is Admin}
    {--test : Has Test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make data grid';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $admin = $this->option('admin');

        if ($admin) {
            $this->makeAdminDataGrid();
        }

        return 1;
    }

    private function makeAdminDataGrid(): void
    {
        $name        = $this->option('name');
        $action      = $this->option('action');
        $package     = $this->argument('package');
        $replacement = $this->getReplacements([
            'ACTION' => \Str::studly($action),
        ]);

        $this->translate(
            'src/Http/Resources/$VERSION$/$NAME$/Admin/$ACTION$DataGrid.php',
            'src/Http/Resources/v1/Admin/DataGrid.stub',
            $replacement
        );

        $driverName = sprintf(
            '%s.%s',
            PackageManager::getAlias($package),
            \Str::kebab($name),
        );
        if ($action) {
            $driverName = sprintf(
                '%s.%s.%s',
                PackageManager::getAlias($package),
                \Str::kebab($name),
                \Str::kebab($action)
            );
        }

        $driverName  = implode('.', array_unique(explode('.', $driverName)));

        $className = $this->translatePath(
            '$PACKAGE_NAMESPACE$\Http\Resources\$VERSION$\$NAME$\Admin\$ACTION$DataGrid',
            $replacement
        );

        $this->updateDrivers([
            'type'        => Constants::DRIVER_TYPE_DATA_GRID,
            'name'        => $driverName,
            'version'     => $this->option('ver'),
            'title'       => 'Data Grid Settings',
            'description' => '',
            'driver'      => $className,
            'resolution'  => $this->option('admin') ? 'admin' : 'web',
            'alias'       => $this->option('alias'),
            'url'         => '',
            'package_id'  => $this->argument('package'),
        ]);
        $this->saveDriversToFiles($package);
        $this->info(sprintf('Updated drivers %s => %s', $driverName, $className));
    }
}
