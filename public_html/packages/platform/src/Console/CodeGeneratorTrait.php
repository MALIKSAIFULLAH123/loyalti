<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Trait PackageCommandTrait.
 *
 * @mixin Command
 * @property array<mixed> $transforms
 */
trait CodeGeneratorTrait
{
    use PackageInfoTrait;

    /**
     * @var string[]
     */
    protected $blockList = [
        '/PrivacyRepositoryInterface\.php/',
        '/PrivacyStreamFactory\.php/',
        '/PrivacyStreamTest\.php/',
        '/PolicyTest\.php/',
        '/TextTest\.php/',
        '/CategoryEmbed\.php/',
        '/CategoryEmbedCollection\.php/',
        '/CategoryDataFactory\.php/',
        '/CategoryDataTest\.php/',
        '/TextFactory\.php/',
        '/TextFactoryTest\.php/',
        '/CategoryEmbedTest\.php/',
        '/CategoryEmbedCollectionTest\.php/',
        '/CategoryDataFactoryTest\.php/',
        '/CategoryRepositoryTest\.php/',
    ];

    /**
     * Execute the console command.
     *
     * @param string               $saveTo
     * @param string|array         $stubs
     * @param array<string, mixed> $replacements
     *
     * @return int
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function translate(string $saveTo, $stubs, array $replacements): int
    {
        $overwriteFile = (bool) $this->option('overwrite');
        $dry           = $this->hasOption('dry') && $this->option('dry');
        $packagePath   = $this->getPackagePath();
        $toFile        = $this->translatePath($saveTo, $replacements);
        $path          = str_replace(
            '\\',
            '/',
            $packagePath . DIRECTORY_SEPARATOR . $toFile
        );

        $path = base_path($path);

        foreach ($this->blockList as $pattern) {
            if (preg_match($pattern, $path)) {
                $this->info(sprintf('File : %s skipped.', $this->stripBasePath($path)));

                return 0;
            }
        }

        if (!$this->option('test')) {
            if (preg_match('/Test\.php/', $path)) {
                $this->info(sprintf('File : %s skipped.', $this->stripBasePath($path)));

                return 0;
            }
        }

        $dir = dirname($path);

        if (!$this->laravel['files']->isDirectory($dir)) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }

        $basePath = app_path('Console/Commands/stubs/');

        Stub::setBasePath($basePath);

        /** @var string $contents */
        $contents = null;
        if (is_string($stubs)) {
            $stubs = [$stubs];
        }

        $toStub = str_replace('.php', '.stub', $toFile);

        array_unshift($stubs, $toStub);

        foreach ($stubs as $stub) {
            if ($contents) {
                continue;
            }
            if (file_exists($basePath . $stub)) {
                $replacements['STUB'] = $stub;
                $contents             = (new Stub($stub, $replacements))->render();
            }
        }

        $exists = file_exists($path);

        if (!$exists) {
            $this->info(sprintf('Created : %s', $this->stripBasePath($path)));
        }

        if ($exists && !$overwriteFile) {
            $this->info(sprintf('Skipped %s', $this->stripBasePath($path)));

            return 0;
        }

        if ($exists && $overwriteFile) {
            $this->info(sprintf('Updated %s', $this->stripBasePath($path)));
        }

        if ($dry && !$exists) {
            return 0;
        }

        try {
            if (!$contents) {
                throw new RuntimeException('Invalid stub ' . var_export($stubs, true));
            }

            (new FileGenerator($path, $contents))
                ->withFileOverwrite($overwriteFile)->generate();
        } catch (FileAlreadyExistException) {
            $this->error(sprintf('File : %s already exists.', $this->stripBasePath($path)));

            return E_ERROR;
        }

        return 0;
    }

    protected function stripBasePath(string $path): string
    {
        return '.' . substr($path, strlen(base_path()));
    }

    /**
     * @param string               $path
     * @param array<string, mixed> $replacements
     *
     * @return string
     */
    public function translatePath(string $path, array $replacements): string
    {
        foreach ($replacements as $search => $replace) {
            $path = str_replace('$' . strtoupper($search) . '$', $replace, $path);
        }

        return $path;
    }

    /**
     * @return string[]
     */
    public function getTransforms(): array
    {
        return $this->transforms;
    }

    /**
     * @param array<string,string> $customs
     *
     * @return array<string, mixed>
     */
    protected function getReplacements(array $customs = []): array
    {
        $packageName = $this->argument('package');

        // $vendorName = $data[0];
        if (isset($customs['name'])) {
            $name = $customs['name'];
        } else {
            $name = $this->option('name');
        }

        $studlyName = Str::studly($name);

        return array_merge([
            'VERSION'                   => 'v1',
            'PACKAGE_NAME'              => $packageName,
            'NAME'                      => $studlyName,
            'NAME_SNAKE'                => Str::snake($name),
            'PACKAGE_NAMESPACE'         => $this->getPackageNamespace(),
            'PACKAGE_ALIAS'             => $name,
            'PACKAGE_STUDLY'            => $studlyName,
            'AUTHOR_NAME'               => 'MetaFox',
            'AUTHOR_EMAIL'              => 'developer@metafoxapp.com',
            'AUTHOR_URL'                => 'https://metafoxapp.com',
            'ESCAPED_PACKAGE_NAMESPACE' => $this->getEscapedPackageNamespace(),
            'INTERNAL_URL'              => '/' . $name,
            'INTERNAL_ADMIN_URL'        => "/$name/setting",
        ], $customs);
    }

    /**
     * @return array<mixed>
     */
    public function getArguments()
    {
        return [
            ['package', InputArgument::OPTIONAL, 'The package name. Example: metafox/blog'],
        ];
    }

    protected function updateDrivers(array $params)
    {
        $drivers = resolve(DriverRepositoryInterface::class);

        try {
            $wheres = Arr::only($params, ['type', 'name', 'version', 'is_admin']);
            $drivers->updateOrCreate($wheres, $params);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function saveDriversToFiles(string $package)
    {
        $drivers = resolve(DriverRepositoryInterface::class);
        $drivers->exportDriverToFilesystem($package);
    }

    /**
     * @param bool                $admincp
     * @param string              $name
     * @param string              $action
     * @param string              $version
     * @param array<string,mixed> $config
     */
    protected function generateFormRequest(
        bool $admincp,
        string $name,
        string $action,
        string $version,
        array $config = []
    ): void {
        $studlyName   = Str::studly($name);
        $studlyAction = Str::studly($action);
        $snakeVersion = Str::snake($version, 'x');

        $replacements = $this->getHttpResourceReplacements($admincp, $name, $version, $action, $config);

        $testVariant = str_replace('-', '_', Str::snake($action));

        $requestPath = $admincp ?
            "Http/Requests/$snakeVersion/$studlyName/Admin/{$studlyAction}Request" :
            "Http/Requests/$snakeVersion/$studlyName/{$studlyAction}Request";

        $this->translate(
            "src/{$requestPath}.php",
            '/packages/requests/api_action_request.stub',
            $replacements,
        );

        // try
        $testRequestPath = $admincp ?
            "Http/Requests/$snakeVersion/$studlyName/Admin/{$studlyAction}Request" :
            "Http/Requests/$snakeVersion/$studlyName/{$studlyAction}Request";

        if (in_array($action, ['show'])) {
            $this->translate(
                "tests/Unit/{$testRequestPath}Test.php",
                "/packages/requests/api_action_{$testVariant}_test.stub",
                $replacements,
            );
        }

        $this->translate(
            "tests/Unit/{$testRequestPath}Test.php",
            '/packages/requests/api_action_request_test.stub',
            $replacements,
        );

        // generate controller test case
        $testActionPath = $admincp ?
            "Http/Controllers/Api/{$snakeVersion}/{$studlyName}/Admin/Action{$studlyAction}" :
            "Http/Controllers/Api/{$snakeVersion}/{$studlyName}/Action{$studlyAction}";

        $this->translate(
            "tests/Unit/{$testActionPath}Test.php",
            '/packages/controllers/api_controller_action_test.stub',
            $replacements,
        );
    }

    /**
     * @param bool                 $admincp
     * @param string               $name
     * @param string               $version
     * @param string               $action
     * @param array<string, mixed> $config
     * @param array<string,mixed>  $extras
     *
     * @return array<string,string>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getHttpResourceReplacements(
        bool $admincp,
        string $name,
        string $version,
        string $action,
        array $config = [],
        array $extras = []
    ): array {
        $studlyAction = Str::studly($action);
        $studlyName   = Str::studly($name);

        return array_merge([
            'NAME'              => $studlyName,
            'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
            'PACKAGE_ALIAS'     => $this->getPackageAlias(),
            'VERSION'           => $version,
            'ADMIN'             => $admincp ? 'Admin' : '',
            'ADMIN_SLASH'       => $admincp ? '\Admin' : '',
            'ADMIN_DIR'         => $admincp ? '/Admin' : '',
            'ACTION'            => $studlyAction,
            'ACTION_LOWER'      => lcfirst($studlyAction),
            'API_PREFIX'        => $admincp ? '/api/v1/admincp' : '/api/v1',
        ], $extras);
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function ensureRepository(string $name, array $config = []): void
    {
        $studlyName = Str::studly($name);

        $this->translate(
            "src/Repositories/{$studlyName}RepositoryInterface.php",
            '/packages/repositories/interface.stub',
            [
                'NAME'              => $studlyName,
                'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                'PACKAGE_ALIAS'     => $this->getPackageAlias(),
            ],
        );

        $this->translate(
            "src/Repositories/Eloquent/{$studlyName}Repository.php",
            ['/packages/repositories/eloquent_repository.stub'],
            [
                'NAME'              => $studlyName,
                'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                'PACKAGE_ALIAS'     => $this->getPackageAlias(),
            ],
        );

        $packagePath    = $this->getPackagePath();
        $folderTestPath = str_replace(
            '\\',
            '/',
            $packagePath . DIRECTORY_SEPARATOR . "tests/Unit/Repositories/Eloquent/{$studlyName}Repository"
        );
        // If already has a folder test, skip this.
        if (!File::exists($folderTestPath)) {
            $this->translate(
                "tests/Unit/Repositories/Eloquent/{$studlyName}RepositoryTest.php",
                '/packages/repositories/eloquent_repository_test.stub',
                [
                    'NAME'              => $studlyName,
                    'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                    'PACKAGE_ALIAS'     => $this->getPackageAlias(),
                ],
            );
        }
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    protected function ensureEloquentModel(string $name, array $config = []): void
    {
        $studlyName = Str::studly($name);
        $table      = $this->option('table');
        $entityType = $this->option('entity');

        $this->translate(
            "src/Models/{$studlyName}.php",
            '/packages/models/model.stub',
            [
                'NAME'              => $studlyName,
                'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                'PACKAGE_ALIAS'     => $this->getPackageAlias(),
                'TABLE'             => $table,
                'ENTITY_TYPE'       => $entityType,
            ],
        );
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function ensureEloquentModelTest(string $name, array $config = []): void
    {
        $name = Str::studly($name);

        $this->translate(
            "tests/Unit/Models/{$name}Test.php",
            '/packages/models/model_test.stub',
            [
                'NAME'              => $name,
                'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                'PACKAGE_ALIAS'     => $this->getPackageAlias(),
            ],
        );
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    protected function ensureEloquentModelFactory(string $name, array $config = []): void
    {
        $studlyName = Str::studly($name);

        $this->translate(
            "src/Database/Factories/{$studlyName}Factory.php",
            '/packages/database/factory.stub',
            [
                'NAME'              => $studlyName,
                'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                'PACKAGE_ALIAS'     => $this->getPackageAlias(),
            ],
        );
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    protected function ensureEloquentModelObserver(string $name, array $config = []): void
    {
        $studlyName = Str::studly($name);

        $this->translate(
            "src/Observers/{$studlyName}Observer.php",
            '/packages/observers/model_observer.stub',
            [
                'NAME'              => $studlyName,
                'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                'PACKAGE_ALIAS'     => $this->getPackageAlias(),
            ],
        );
    }

    protected function ensurePackageSettingsListener(): void
    {
        $this->translate(
            'src/Listeners/PackageSettingListener.php',
            '/scaffold/listener_settings.stub',
            [
                'PACKAGE_NAME'      => $this->getPackageName(),
                'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                'PACKAGE_ALIAS'     => $this->getPackageAlias(),
            ],
        );
    }

    /**
     * @param bool                 $admincp
     * @param string               $name
     * @param string               $version
     * @param array<string, mixed> $config
     */
    protected function ensureResourceApiGateway(bool $admincp, string $name, string $version, array $config = []): void
    {
        $studlyName   = Str::studly($name);
        $replacements = $this->getHttpResourceReplacements($admincp, $name, $version, '', $config);

        $path = $admincp ?
            "Http/Controllers/Api/{$studlyName}AdminController" :
            "Http/Controllers/Api/{$studlyName}Controller";

        $this->translate(
            "src/{$path}.php",
            '/packages/controllers/api_gateway.stub',
            $replacements,
        );

        $this->ensureResourceApiController($admincp, $name, $version, $config);
    }

    /**
     * @param bool                 $admincp
     * @param string               $name
     * @param string               $version
     * @param array<string, mixed> $config
     */
    protected function ensureResourceApiController(
        bool $admincp,
        string $name,
        string $version,
        array $config = []
    ): void {
        $studlyName = Str::studly($name);

        $version = Str::snake($version ?? 'v1', 'x');

        $replacements = $this->getHttpResourceReplacements($admincp, $name, $version, '', $config);

        $saveTo = $admincp ?
            "Http/Controllers/Api/{$version}/{$studlyName}AdminController" :
            "Http/Controllers/Api/{$version}/{$studlyName}Controller";

        $this->translate(
            "src/{$saveTo}.php",
            '/packages/controllers/api_controller.stub',
            $replacements,
        );
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    protected function ensureEloquentModelPolicy(string $name, array $config = []): void
    {
        $studlyName = Str::studly($name);
        $entityType = $this->option('entity');

        $replacements = [
            'NAME'              => $studlyName,
            'ENTITY_TYPE'       => $entityType,
            'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
            'PACKAGE_ALIAS'     => $this->getPackageAlias(),
        ];

        $this->translate(
            "src/Policies/{$studlyName}Policy.php",
            '/packages/policies/model_policy.stub',
            $replacements,
        );

        $packagePath    = $this->getPackagePath();
        $folderTestPath = str_replace(
            '\\',
            '/',
            $packagePath . DIRECTORY_SEPARATOR . "tests/Unit/Policies/{$studlyName}Policy"
        );
        // If already has a folder test, skip this.
        if (!File::exists($folderTestPath)) {
            $this->translate(
                "tests/Unit/Policies/{$studlyName}PolicyTest.php",
                '/packages/policies/model_policy_test.stub',
                $replacements,
            );
        }
    }

    /**
     * @param bool                $admincp
     * @param string              $name
     * @param string              $variant
     * @param string              $version
     * @param array<string,mixed> $config
     */
    protected function generateResourceVariant(
        bool $admincp,
        string $name,
        string $variant,
        string $version,
        array $config = []
    ): void {
        $studlyName    = Str::studly($name);
        $studlyVariant = Str::studly($variant); // Item, Embed, Detail
        $version       = Str::snake($version, 'x');
        $replacements  = $this->getHttpResourceReplacements($admincp, $name, $version, $variant, $config);
        $stubName      = $variant;

        if (!in_array($stubName, ['item', 'embed', 'detail'])) {
            $stubName = 'item';
        }

        $itemPath = $admincp ?
            "Http/Resources/{$version}/{$studlyName}/Admin/{$studlyName}{$studlyVariant}" :
            "Http/Resources/{$version}/{$studlyName}/{$studlyName}{$studlyVariant}";

        $collectionPath = $admincp ?
            "Http/Resources/$version/{$studlyName}/Admin/{$studlyName}{$studlyVariant}Collection" :
            "Http/Resources/$version/{$studlyName}/{$studlyName}{$studlyVariant}Collection";

        $this->translate(
            "src/{$itemPath}.php",
            "/packages/resources/{$stubName}.stub",
            $replacements
        );

        if (!in_array($variant, ['detail'])) {
            $this->translate(
                "src/{$collectionPath}.php",
                "/packages/resources/{$stubName}_collection.stub",
                $replacements
            );
        }

        $this->translate(
            "tests/Unit/{$itemPath}Test.php",
            "/packages/resources/{$stubName}_test.stub",
            $replacements
        );
    }

    /**
     * @param bool                $admincp
     * @param string              $name
     * @param string              $variant
     * @param string              $version
     * @param array<string,mixed> $config
     */
    protected function generateResourceSettings(
        bool $admincp,
        string $name,
        string $variant,
        string $version,
        array $config = []
    ): void {
        $studlyName    = Str::studly($name);
        $studlyVariant = Str::studly($variant); // Item, Embed, Detail
        $version       = Str::snake($version, 'x');

        $base = strstr($variant, 'mobile') > -1 ? 'mobile' : 'web';

        $replacements = $this->getHttpResourceReplacements($admincp, $name, $version, $variant, $config);

        $replacements['BASE'] = Str::studly($base);

        if ($admincp) {
            $this->translate(
                "src/Http/Resources/{$version}/{$studlyName}/Admin/{$studlyVariant}Setting.php",
                '/packages/resources/resource_admin_setting.stub',
                $replacements,
            );
            $this->translate(
                "tests/Unit/Http/Resources/{$version}/{$studlyName}/Admin/{$studlyVariant}SettingTest.php",
                '/packages/resources/resource_setting_test.stub',
                $replacements,
            );
        } else {
            $this->translate(
                "src/Http/Resources/{$version}/{$studlyName}/{$studlyVariant}Setting.php",
                "/packages/resources/resource_{$variant}_setting.stub",
                $replacements
            );

            $this->translate(
                "tests/Unit/Http/Resources/{$version}/{$studlyName}/{$studlyVariant}SettingTest.php",
                '/packages/resources/resource_setting_test.stub',
                $replacements,
            );
        }
    }

    /**
     * @param string $studlyName
     * @param string $table
     * @param string $entityType
     */
    protected function createModelText(string $studlyName, string $table, string $entityType): void
    {
        $this->translate(
            "src/Models/{$studlyName}Text.php",
            '/packages/models/model_text.stub',
            [
                'NAME'              => $studlyName,
                'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                'PACKAGE_ALIAS'     => $this->getPackageAlias(),
                'TABLE'             => $table,
                'ENTITY_TYPE'       => $entityType,
            ],
        );
    }

    /**
     * @param string $studlyName
     * @param string $table
     * @param string $entityType
     */
    protected function ensureCategoryData(string $studlyName, string $table, string $entityType): void
    {
        $this->translate(
            "src/Models/{$studlyName}CategoryData.php",
            '/packages/models/model_category_data.stub',
            [
                'NAME'              => $studlyName,
                'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                'PACKAGE_ALIAS'     => $this->getPackageAlias(),
                'TABLE'             => $table,
                'ENTITY_TYPE'       => $entityType,
            ],
        );
    }

    /**
     * @param string $studlyName
     * @param string $table
     * @param string $entityType
     */
    protected function createModelTagData(string $studlyName, string $table, string $entityType): void
    {
        $this->translate(
            "src/Models/{$studlyName}TagData.php",
            '/packages/models/model_tag_data.stub',
            [
                'NAME'              => $studlyName,
                'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                'PACKAGE_ALIAS'     => $this->getPackageAlias(),
                'TABLE'             => $table,
                'ENTITY_TYPE'       => $entityType,
            ],
        );
    }

    /**
     * @param string $studlyName
     * @param string $table
     * @param string $entityType
     */
    protected function createModelPrivacyStream(string $studlyName, string $table, string $entityType): void
    {
        $this->translate(
            "src/Models/{$studlyName}PrivacyStream.php",
            '/packages/models/model_privacy_stream.stub',
            [
                'NAME'              => $studlyName,
                'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                'PACKAGE_ALIAS'     => $this->getPackageAlias(),
                'TABLE'             => $table,
                'ENTITY_TYPE'       => $entityType,
            ],
        );
    }

    /**
     * @param string $studlyName
     * @param string $table
     * @param string $entityType
     */
    protected function createModelNetworkStream(string $studlyName, string $table, string $entityType): void
    {
        $this->translate(
            "src/Models/{$studlyName}NetworkStream.php",
            '/packages/models/model_network_stream.stub',
            [
                'NAME'              => $studlyName,
                'PACKAGE_NAMESPACE' => $this->getPackageNamespace(),
                'PACKAGE_ALIAS'     => $this->getPackageAlias(),
                'TABLE'             => $table,
                'ENTITY_TYPE'       => $entityType,
            ],
        );
    }

    /**
     * @param bool                $admincp
     * @param string              $name
     * @param string              $action
     * @param string              $version
     * @param array<string,mixed> $config
     */
    private function generateForm(bool $admincp, string $name, string $action, string $version, array $config): void
    {
        $studlyName   = Str::studly($name);
        $studlyAction = Str::studly($action);

        if (Str::endsWith($studlyAction, 'Form')) {
            $studlyAction = str_replace('Form', '', $studlyAction);
        }

        $className = count(explode('-', Str::kebab($studlyAction))) > 1 ?
            "{$studlyAction}Form" :
            "{$studlyAction}{$studlyName}Form";

        $replacements = $this->getHttpResourceReplacements($admincp, $name, $version, $action, $config, [
            'CLASS_NAME' => $className,
        ]);

        $path = $admincp ?
            "Http/Resources/{$version}/{$studlyName}/Admin/{$className}" :
            "Http/Resources/{$version}/{$studlyName}/{$className}";

        $this->translate(
            "src/{$path}.php",
            [
                $admincp ? "/packages/resources/{$version}/{$studlyName}/Admin/{$className}.stub" :
                    "/packages/resources/{$version}/{$studlyName}/{$className}.stub",
                $admincp ? "/packages/resources/v1/{$studlyName}/Admin/{$className}.stub" :
                    "/packages/resources/v1/{$studlyName}/{$className}.stub",
                '/packages/resources/edit_form.stub',
            ],
            $replacements,
        );

        $this->translate(
            "tests/Unit/{$path}Test.php",
            '/packages/resources/edit_form_test.stub',
            $replacements,
        );
    }
}
