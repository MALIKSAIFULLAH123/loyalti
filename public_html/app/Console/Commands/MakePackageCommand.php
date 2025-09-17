<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MetaFox\Platform\Console\CodeGeneratorTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MakePackageCommand.
 * @codeCoverageIgnore
 */
class MakePackageCommand extends Command
{
    use CodeGeneratorTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'package:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new package.';

    protected string $packagePath = '';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $package = $this->argument('package');

        $this->packagePath = implode(DIRECTORY_SEPARATOR, [
            'packages',
            $package,
        ]);

        $this->generateFolders();

        $this->generateFiles();

        $this->call('package:install', [
            'package' => $package
        ]);

        return 0;
    }

    public function getPackageAlias(): string
    {
        return '';
    }

    public function getPackagePath(): string
    {
        return $this->packagePath;
    }

    public function getPackageNamespace(): string
    {
        $vendor = $this->option('vendor');
        $name = $this->option('name');

        return Str::studly($vendor).'\\'.Str::studly($name);
    }

    /**
     * Get the list of folders.
     *
     * @return array<mixed>
     */
    protected function getFolders(): array
    {
        $type = $this->option('type');

        return match ($type) {
            'language' => [
                'src/Providers',
                'src/Listeners',
                'config',
                'resources/lang/'.$this->option('language_code'),
            ],
            'theme' => [
                'src/Providers',
                'src/Listeners',
                'config',
                'resources/images',
            ],
            default => [
                'src/Http',
                'src/Http/Controllers',
                'src/Http/Requests',
                'src/Http/Resources/v1',
                'src/Models',
                'src/Providers',
                'src/Repositories',
                'src/Repositories/Eloquent',
                'config',
                'routes',
                'resources/assets',
                'resources/lang',
                'resources/menu',
                'tests/Tests/Unit',
                'tests/Tests/Feature',
            ],
        };
    }

    /**
     * Get the list of files created.
     *
     * @return array<mixed>
     */
    protected function getFiles(): array
    {
        $type = $this->option('type');

        return match ($type) {
            'language' => [
                'packages/config/config'      => 'config/config.php',
                'lang-composer'               => 'composer.json',
                'scaffold/listener_settings'  => 'src/Listeners/PackageSettingListener.php',
                'packages/providers/provider' => 'src/Providers/PackageServiceProvider.php',
            ],
            'theme' => [
                'packages/config/theme-config' => 'config/config.php',
                'theme-composer'               => 'composer.json',
                'scaffold/listener_settings'   => 'src/Listeners/PackageSettingListener.php',
                'packages/providers/provider'  => 'src/Providers/PackageServiceProvider.php',
            ],
            default => [
                'routes/api'                        => 'routes/api.php',
                'routes/api-admin'                  => 'routes/api-admin.php',
                'resources/lang/phrase'             => 'resources/lang/en/phrase.php',
                'resources/lang/permission'         => 'resources/lang/en/permission.php',
                'resources/lang/validation'         => 'resources/lang/en/validation.php',
                'resources/lang/admin'              => 'resources/lang/en/admin.php',
                'resources/drivers'                 => 'resources/drivers.php',
                'resources/menu/web'                => 'resources/menu/web.php',
                'resources/menu/admin'              => 'resources/menu/admin.php',
                'resources/menu/menus'              => 'resources/menu/menus.php',
                'packages/config/config'            => 'config/config.php',
                'composer'                          => 'composer.json',
                'scaffold/listener_settings'        => 'src/Listeners/PackageSettingListener.php',
                'packages/providers/provider'       => 'src/Providers/PackageServiceProvider.php',
                'src/Http/v1/PackageSetting'        => 'src/Http/Resources/v1/PackageSetting.php',
                'src/Http/v1/Admin/SiteSettingForm' => 'src/Http/Resources/v1/Admin/SiteSettingForm.php',
                'packages/database/seeder-database' => 'src/Database/Seeders/PackageSeeder.php',
            ]
        };
    }

    /**
     * Generate the folders.
     */
    public function generateFolders(): void
    {
        foreach ($this->getFolders() as $folder) {
            $path = base_path($this->getPackagePath().DIRECTORY_SEPARATOR.$folder);

            Log::channel('dev')->info('make dir', [$path]);

            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true, true);
            }
        }
    }

    /**
     * Generate the files.
     */
    public function generateFiles(): void
    {
        foreach ($this->getFiles() as $stub => $file) {
            if (!is_string($stub)) {
                continue;
            }

            if (!str_contains($stub, '.stub')) {
                $stub = $stub.'.stub';
            }

            $this->translate(
                $file,
                $stub,
                $this->getReplacements(),
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getReplacements(): array
    {
        $packageName = $this->argument('package');
        $name = $this->option('name');
        $type = $this->option('type');
        $themeId = null;

        if (!$type) {
            $type = 'app';
        }

        if ('theme' === $type) {
            $themeId = Str::replace('theme-', '', Str::kebab($name));
        }

        $nameKebab = Str::kebab($name);
        $nameStudly = Str::studly($name);

        return [
            'VERSION'                   => 'v1',
            'PACKAGE_NAME'              => $packageName,
            'NAME'                      => $nameStudly,
            'PACKAGE_TYPE'              => $type ?? 'app',
            'NAME_SNAKE'                => Str::snake($name),
            'NAME_KEBAB'                => $nameKebab,
            'THEME_ID'                  => $themeId,
            'PACKAGE_NAMESPACE'         => $this->getPackageNamespace(),
            'PACKAGE_ALIAS'             => $nameKebab,
            'PACKAGE_STUDLY'            => $nameStudly,
            'AUTHOR_NAME'               => $this->option('author'),
            'AUTHOR_URL'                => $this->option('homepage'),
            'ESCAPED_PACKAGE_NAMESPACE' => $this->getEscapedPackageNamespace(),
            'INTERNAL_URL'              => '/'.$nameKebab,
            'INTERNAL_ADMIN_URL'        => "/{$nameKebab}/setting",
            'DIRECTION'                 => $this->option('direction'),
            'TITLE'                     => $this->option('title'),
            'LANGUAGE_CODE'             => $this->option('language_code'),
            'CHARSET'                   => 'utf8',
        ];

    }

    /**
     * @return array<mixed>
     */
    protected function getArguments(): array
    {
        return [
            ['package', InputArgument::REQUIRED, 'Package name, etc: metafox/blog, metafox/video'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['vendor', null, InputOption::VALUE_REQUIRED, 'Vendor of namespace, etc: MetaFox, Social', null],
            ['name', null, InputOption::VALUE_REQUIRED, 'Name of namespace, etc: Activity, VirtualGift', null],
            ['type', null, InputOption::VALUE_OPTIONAL, 'Type of package: etc: app, theme, language', 'app'],
            ['homepage', null, InputOption::VALUE_OPTIONAL, 'Url to author home page', null],
            ['author', null, InputOption::VALUE_OPTIONAL, 'Author name of package', null],
            ['title', null, InputOption::VALUE_OPTIONAL, 'Title of package', null],
            ['direction', null, InputOption::VALUE_OPTIONAL, 'Language direction', null],
            ['language_code', null, InputOption::VALUE_OPTIONAL, 'Language Code', null],
            ['base_language', null, InputOption::VALUE_OPTIONAL, 'Base Language', 'en'],
            ['dry', null, InputOption::VALUE_OPTIONAL, 'Don\'t write to filesystem', false],
            ['test', null, InputOption::VALUE_OPTIONAL, 'Generate unitest classes', false],
            ['overwrite', null, InputOption::VALUE_OPTIONAL, 'Overwrite existing file', false],
        ];
    }
}
