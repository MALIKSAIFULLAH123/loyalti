<?php

namespace App\Console\Commands;

use Brick\VarExporter\VarExporter;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use MetaFox\Authorization\Traits\HasRoles;
use MetaFox\Platform\Console\CodeGeneratorTrait;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\User;
use Symfony\Component\Console\Input\InputOption;

class MakeImporterCommand extends Command
{
    use CodeGeneratorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'package:make-importer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make job class.';

    /**
     * @var array
     */
    protected array $docs = [];

    /**
     * @throws \Brick\VarExporter\ExportException
     */
    public function handle(): int
    {
        if ($this->argument('package')) {
            $this->handlePackage();
        } elseif ($this->option('all')) {
            foreach ([
                'metafox/blog',
                'metafox/user',
                'metafox/authorization',
                'metafox/friend',
                'metafox/forum',
            ] as $package) {
                $this->call('package:make-importer', [
                    'package'     => $package,
                    '--overwrite' => $this->option('overwrite'),
                    '--dry'       => $this->option('dry'),
                ]);
            }
        }

        return 0;
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Brick\VarExporter\ExportException
     */
    public function handlePackage(): int
    {
        $package    = $this->argument('package');
        $namespace  = PackageManager::getNamespace($package);
        $this->docs = [];

        $files = glob(base_path(PackageManager::getPath($package) . '/src/Models/*.php'));
        $ff    = app('files');

        foreach ($files as $file) {
            $name = $ff->name($file);

            // Skip Privacy stream
            if (str_contains($name, 'PrivacyStream')) {
                continue;
            }

            $modelClass = sprintf('%s\\Models\\%s', $namespace, $name);

            /** @var User $modelInstance */
            $modelInstance = new $modelClass();

            if (!$modelInstance instanceof Model) {
                continue;
            }

            if ($modelInstance instanceof Pivot && str_ends_with(get_class($modelInstance), 'TagData')) {
                continue;
            }

            if ($modelInstance instanceof ResourceText) {
                continue;
            }

            /** @var string[] $fillable */
            $columns = array_unique([
                ...$modelInstance->getFillable(),
                ...Schema::getColumnListing($modelInstance->getTable()),
            ]);
            $fillable  = [];
            $relations = [];

            $nestedAttributes = $modelInstance->nestedAttributes;
            $fileColumns      = $modelInstance->fileColumns;
            $skipColumns      = ['server_id', 'id', 'package_id', 'image_path'];

            foreach ($columns as $column) {
                if (in_array($column, $skipColumns)) {
                    // skip fields
                } elseif (str_ends_with($column, '_file_id')) {
                    if (is_array($fileColumns) && array_key_exists($column, $fileColumns)) {
                        // do thinging
                    } else {
                        // do nothing
                        $relations[] = Str::camel(substr($column, 0, -8));
                    }
                } elseif (str_ends_with($column, '_id')) {
                    $relations[] = Str::camel(substr($column, 0, -3));
                } elseif (in_array($column, ['currency'])) {
                    $relations[] = $column;
                } else {
                    $fillable[] = $column;
                }
            }

            if (is_array($nestedAttributes)) {
                $fillable = array_unique([...$fillable, ...Arr::flatten($nestedAttributes)]);
            }

            $traits = class_uses_recursive($modelClass);

            // check has owner
            if (in_array(HasOwnerMorph::class, $traits)) {
                // has owner
                $relations[] = 'owner';

                // drop owner_id, owner_type
                $fillable = array_diff($fillable, ['owner_id', 'owner_type']);
            }

            if (in_array(HasUserMorph::class, $traits)) {
                // has owner
                $relations[] = 'user';

                // drop user_id, user_type
                $fillable = array_diff($fillable, ['user_id', 'user_type']);
            }

            if (in_array(HasItemMorph::class, $traits)) {
                // has owner
                $relations[] = 'item';

                // drop user_id, user_type
                $fillable = array_diff($fillable, ['item_id', 'item_type']);
            }

            if (in_array(HasRoles::class, $traits)) {
                // has owner
                $relations[] = 'role';
            }

            if ($fileColumns && is_array($fileColumns)) {
                $relations = array_unique([...$relations, ...array_values($fileColumns)]);
                // strip fields
                $fillable = array_diff($fillable, array_keys($fileColumns));
            }

            $relations = array_unique($relations);
            $fillable  = array_unique($fillable);

            $this->makeCode($name, $fillable, $relations);
            $this->docs[] = $this->getDoc($name, $modelInstance, $fillable, $relations, $fileColumns);
        }

        $this->makeMdx();

        return 0;
    }

    public function getFileSpec()
    {
        return [
            '$id'      => 'file#9fa09463c063222085085c050c86a8ca',
            'origin'   => 'pic/event/2021/04/6d31b907b70e158364ddac0fb963e75f.png',
            'storage'  => 'phpfox:0',
            'variants' => [
                [
                    'variant' => '200',
                    'storage' => 'phpfox:0',
                    'path'    => 'pic/event/2021/04/6d31b907b70e158364ddac0fb963e75f_200_square.png',
                ],
                [
                    'variant' => '500',
                    'storage' => 'phpfox:0',
                    'path'    => 'pic/event/2021/04/6d31b907b70e158364ddac0fb963e75f_500.png',
                ],
            ],
        ];
    }

    public function makeMdx()
    {
        $alias    = Str::snake(PackageManager::getAlias($this->getPackageName()));
        $dirname  = base_path('packages/metafox/importer/docs');
        $filename = $dirname . '/' . $alias . '.mdx';

        file_put_contents($filename, implode(PHP_EOL . PHP_EOL, $this->docs));
    }

    /**
     * @param         $name
     * @param  User   $modelInstance
     * @param         $fillable
     * @param         $relations
     * @param         $fileColumns
     * @return string
     */
    public function getDoc($name, $modelInstance, $fillable, $relations, $fileColumns): string
    {
        $tableName = $modelInstance->getTable();
        $columns   = Schema::getColumnListing($tableName);

        $casts = $modelInstance->getCasts();

        $entry = [
            '$id' => "$tableName#id",
        ];

        foreach ($relations as $relation) {
            $entry["\$$relation"] = "$relation#id";
        }

        foreach ($fillable as $column) {
            $entry[$column] = 'string';

            if (array_key_exists($column, $casts)) {
                $entry[$column] = $casts[$column];
            } elseif (in_array($column, $columns)) {
                $entry[$column] = Schema::getColumnType($tableName, $column);
            }
        }

        if (is_array($fileColumns)) {
            foreach ($fileColumns as $relation) {
                $entry["\$$relation"] = 'file#id....';
            }
        }

        $phpArray[] = '[';
        foreach ($entry as $column => $value) {
            $left = str_pad("    '$column'", 40);

            if ($column == '$id') {
                $phpArray[] = "$left=>  '$tableName#'.\$row['id'],";
            } elseif (str_starts_with($column, '$')) {
                $phpArray[] = "$left=>  '$value',";
            } elseif (str_ends_with($column, '_at')) {
                $phpArray[] = "$left=>  date('c', \$row['$column']),";
            } else {
                $phpArray[] = "$left=>  \$row['$column'],";
            }
        }

        $phpArray[] = ']';
        $jsonSpec   = json_encode([$entry], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $phpSpec    = implode(PHP_EOL, $phpArray);

        return sprintf('
### %s

```json
%s
```

```php
$row = [];

return %s
```
', $name, $jsonSpec, $phpSpec);
    }

    /**
     * @param  string                             $name
     * @param  string[]                           $fillable
     * @param  string[]                           $relations
     * @return void
     * @throws \Brick\VarExporter\ExportException
     */
    public function makeCode(string $name, array $fillable, array $relations)
    {
        $this->translate(
            'src/Database/Importers/$NAME$Importer.php',
            'packages/database/json-importer.stub',
            $this->getReplacements([
                'name'      => $name,
                'NAME'      => $name,
                'FILLABLE'  => VarExporter::export(array_values($fillable)),
                'RELATIONS' => VarExporter::export(array_values($relations)),
            ])
        );
    }

    /**
     * @return array[]
     * @link \MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeSeederRequest::rules()
     */
    protected function getOptions()
    {
        return [
            ['overwrite', null, InputOption::VALUE_NONE, 'Overwrite existing files?.'],
            ['dry', null, InputOption::VALUE_NONE, 'Dry run test class?.'],
            ['all', null, InputOption::VALUE_NONE, 'Generate all package'],
            ['test', null, InputOption::VALUE_NONE, 'Also generate test class?.'],
            ['ver', null, InputOption::VALUE_OPTIONAL, 'Version?.', false],
        ];
    }
}
