<?php

namespace MetaFox\Platform;

use Closure;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MetaFox\App\Models\Package;
use MetaFox\App\Repositories\Eloquent\PackageRepository;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Support\BasePackageSettingListener;

class PackageManagerImpl
{
    /**
     * Migrations folder.
     */
    public const MIGRATION_PATH = '/src/Database/Migrations';

    /**
     * Configure Path.
     */
    public const CONFIG_PATH = '/config/config.php';

    /**
     * where to storage assets.
     */
    public const ASSETS_PATH = '/resources/assets';

    /**
     * @var BasePackageSettingListener[]
     */
    private array $listeners;

    /**
     * @var string[]
     */
    private array $providers;

    /**
     * @var string[]
     */
    private mixed $moduleNames;

    /**
     * @var array<string,boolean>
     */
    private array $activePackages;

    /**
     * @var array<string,string>
     */
    private array $titleMap;

    /**
     * @var array
     */
    private array $aliasForEntities = [];

    /**
     * Backward compatible to ModuleManager before 5.1.4.
     * @return $this
     */
    public function instance()
    {
        return $this;
    }

    /**
     * @return BasePackageSettingListener[]
     */
    private function getListeners(): array
    {
        if (!isset($this->listeners)) {
            $this->listeners = $this->scanListeners();
        }

        return $this->listeners;
    }

    /**
     * discover module settings collect settings from all active module PackageSettingListener class
     * to an array.
     *
     * etc: PackageManager::discoverSettings('getEvents')
     *
     * @param string $name
     * @param bool   $cache
     *
     * @return array<mixed>
     */
    public function discoverSettings(string $name, $cache = true): array
    {
        if (!$cache) {
            return $this->getSettings($name);
        }

        return localCacheStore()->rememberForever(sprintf('discoverSettings_%s', $name), function () use ($name) {
            return $this->getSettings($name);
        });
    }

    /**
     * Get provider names in schema packages.
     * @return string[]
     * @see \App\Providers\AppServiceProvider::discoverPackageProviders()
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Get all packages events.
     *
     * @return array<string, array<int, mixed>> ["eventName"=> [Listener::class, ...]]
     *
     * @see \App\Providers\EventServiceProvider::discoverPackageEvents()
     */
    public function getEvents(): array
    {
        $response = $this->getSettings('getEvents');
        $data     = [];

        if (!$response) {
            return $data;
        }
        foreach ($response as $row) {
            if (!$row) {
                continue;
            }
            foreach ($row as $event => $listeners) {
                if (!$listeners) {
                    continue;
                }
                foreach ($listeners as $listener) {
                    if ($listener) {
                        $data[$event][] = $listener;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @return string[]|null
     * @see \App\Providers\AppServiceProvider::boot()
     */
    public function getDatabaseMigrationsFrom()
    {
        $files = [];

        PackageManager::pluck(function ($package) use (&$files) {
            $modulePath    = $package['path'];
            $module        = $package['alias'];
            $migrationPath = $modulePath . static::MIGRATION_PATH;
            if (is_dir(base_path($migrationPath))) {
                $files[Str::lower($module)] = base_path($migrationPath);
            }
        });

        return $files;
    }

    /**
     * Can be invoked before booted.
     *
     * @param string $name
     *
     * @return array<string, array<string, mixed>>
     */
    private function getSettings(string $name): array
    {
        $result = [];

        $listeners = $this->getListeners();

        foreach ($listeners as $package => $listener) {
            $data  = $listener->handle($name);
            $alias = PackageManager::getAlias($package);
            if (!empty($data)) {
                $result[$alias] = $data;
            }
        }

        return $result;
    }

    /**
     * @return Collection<Package>
     */
    private function getModules(): Collection
    {
        try {
            return Package::query()
                ->where('is_active', '=', MetaFoxConstant::IS_ACTIVE)
                ->get();
        } catch (\Exception) {
        }

        return new Collection();
    }

    private function initModuleNames(): void
    {
        $this->moduleNames = localCacheStore()->rememberForever('initModuleNames', function () {
            $this->getModules()->map(function (Package $module) {
                return $module->name;
            });
        });
    }

    /**
     * @return BasePackageSettingListener[]
     */
    private function scanListeners(): array
    {
        $platformInstalled = config('app.mfox_installed');
        $activePackages    = $this->getModules()->pluck('name')->toArray();

        return PackageManager::pluck(function (array $package) use ($platformInstalled, $activePackages) {
            if ($platformInstalled && !in_array($package['name'], $activePackages)) {
                return;
            }

            $class = sprintf('%s\\Listeners\\PackageSettingListener', $package['namespace']);
            if (class_exists($class)) {
                return new $class();
            }
        });
    }

    public function checkActive(string $name): bool
    {
        if (!isset($this->activePackages) && config('app.mfox_installed')) {
            try {
                $this->activePackages = localCacheStore()->rememberForever(
                    'active_packages',
                    fn () => Package::query()
                        ->where([
                            'is_active'    => 1,
                            'is_installed' => 1,
                        ])->get(['is_active', 'name', 'alias'])
                        ->reduce(function ($carry, $x) {
                            $carry[$x->name]  = $x->is_active;
                            $carry[$x->alias] = $x->is_active;

                            return $carry;
                        }, [])
                );
            } catch (\Throwable) {
                // error throw then metafox does not installed.
            }
        }

        if (isset($this->activePackages)) {
            return $this->activePackages[$name] ?? false;
        }

        $value = config("metafox.packages.$name");

        return !empty($value);
    }

    public function isCore(string $name): bool
    {
        return (bool) config(sprintf('metafox.packages.%s.core', $name), false);
    }

    /**
     * @param  string                    $name
     * @return array<string, mixed>|null
     */
    public function getInfo(string $name): ?array
    {
        return config("metafox.packages.$name");
    }

    public function getName(string $name): string
    {
        $result = config(sprintf('metafox.packages.%s.name', $name));

        if (!$result) {
            $result = self::getByAlias($name);
        }

        return $result ?? '';
    }

    public function getTitle(string $name): ?string
    {
        try {
            if (!isset($this->titleMap)) {
                $this->titleMap = Cache::rememberForever(
                    __FUNCTION__,
                    fn () => Package::query()
                    ->get()
                    ->reduce(function ($carry, $x) {
                        $carry[$x->name]  = $x->title;
                        $carry[$x->alias] = $x->title;

                        return $carry;
                    }, [])
                );
            }
        } catch (\Throwable) {
            $this->titleMap = [];
        }

        return $this->titleMap[$name] ?? config(sprintf('metafox.packages.%s.title', $name));
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getNamespace(string $name): string
    {
        $value = config(sprintf('metafox.packages.%s.namespace', $name));

        return trim($value, '\\');
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getAlias(string $name): string
    {
        $value = config(sprintf('metafox.packages.%s.alias', $name));

        return rtrim($value, '\\');
    }

    /**
     * @param  string $entityType
     * @return string
     *                Optimize for other method
     */
    public function getAliasForEntityType(string $entityType): string
    {
        if (empty($this->aliasForEntities)) {
            $this->aliasForEntities = localCacheStore()->rememberForever(
                __FUNCTION__,
                fn () => resolve(DriverRepositoryInterface::class)->getEntityPackageAlias()
            );
        }

        return $this->aliasForEntities[$entityType] ?? $entityType;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getFrontendAlias(string $name): string
    {
        $value = config(sprintf('metafox.packages.%s.frontendAlias', $name));

        return rtrim($value, '\\');
    }

    /**
     * @param  string      $name
     * @param  string|null $for
     * @return string
     */
    public function getAliasFor(string $name, ?string $for = null): string
    {
        $config = config(sprintf('metafox.packages.%s', $name));
        $value  = null;

        switch ($for) {
            case 'mobile':
                $value = $config['mobileAlias'] ?? null;
                break;
            case 'admin':
            case 'web':
                $value = $config['frontendAlias'] ?? null;
                break;
        }

        if (!$value && $config) {
            $value = $config['alias'];
        }

        return rtrim($value, '\\');
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function getPath(string $name): ?string
    {
        $value = config(sprintf('metafox.packages.%s.path', $name));

        if ($value) {
            return rtrim($value, '\\');
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function getComposerJsonPath(string $name): ?string
    {
        $path = self::getPath($name);

        if (!is_string($path)) {
            return null;
        }

        $jsonFile = app()->basePath($path . DIRECTORY_SEPARATOR . 'composer.json');

        if (!file_exists($jsonFile)) {
            return null;
        }

        return $jsonFile;
    }

    /**
     * @param  string     $name
     * @return array|null
     */
    public function getComposerJson(string $name): ?array
    {
        $file = self::getComposerJsonPath($name);

        if (!$file) {
            return null;
        }

        $data = json_decode(mf_get_contents($file), true);

        if (is_array($data)) {
            return $data;
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getBasePath(string $name): string
    {
        $path = self::getPath($name);

        return base_path($path);
    }

    public function getMigrationPath(string $name): string
    {
        return static::getPath($name) . self::MIGRATION_PATH;
    }

    public function getConfigPath(string $name): string
    {
        return static::getPath($name) . self::CONFIG_PATH;
    }

    /**
     * Get internal assets path within packages directory.
     *
     * @param string $name
     *
     * @return string
     */
    public function getAssetPath(string $name): string
    {
        return static::getPath($name) . self::ASSETS_PATH;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getConfig(string $name): array
    {
        $path = base_path(self::getConfigPath($name));

        if (!File::isFile($path)) {
            return [];
        }

        return app('files')->getRequire($path);
    }

    /**
     * @param string $name
     *
     * @return string[]
     */
    public function getMigrations(string $name): array
    {
        $path = static::getMigrationPath($name) . DIRECTORY_SEPARATOR . '*.php';

        /** @var string[] $files */
        $files = app('files')->glob($path);

        // Once we have the array of files in the directory we will just remove the
        // extension and take the basename of the file which is all we need when
        // finding the migrations that haven't been run against the databases.
        if (empty($files)) {
            return [];
        }

        $files = array_map(function ($file) {
            return str_replace('.php', '', basename($file));
        }, $files);

        // Once we have all of the formatted file names we will sort them and since
        // they all start with a timestamp this should give us the migrations in
        // the order they were actually created by the application developers.
        sort($files);

        return $files;
    }

    /**
     * @return string[]
     */
    public function getPackageNames(): array
    {
        $packageNames = [];

        /** @var array<string,mixed> $data */
        $data = config('metafox.packages');

        if (!empty($data)) {
            $packageNames = array_keys($data);
        }

        return $packageNames;
    }

    public function getNameStudly(string $packageName): string
    {
        $value = Str::studly(config(sprintf('metafox.packages.%s.alias', $packageName)));

        return rtrim($value, '\\');
    }

    /**
     * Get the master seeder name in the database.
     *
     * @param string $packageName
     *
     * @return string[]
     */
    public function getMasterSeederClasses(string $packageName): array
    {
        $namespace = static::getNamespace($packageName);

        if ($namespace) {
            $class = sprintf("%s\Database\Seeders\PackageSeeder", $namespace);

            if (class_exists($class)) {
                return [$class];
            }
        }

        return [];
    }

    /**
     * Get the master seeder name in the database.
     *
     * @param string $packageName
     *
     * @return string|null
     */
    public function getSeeder(string $packageName): ?string
    {
        $namespace = static::getNamespace($packageName);

        if ($namespace) {
            $class = sprintf("%s\Database\Seeders\PackageSeeder", $namespace);

            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }

    /**
     * @param Closure $callback
     *
     * @return array<mixed>
     */
    public function pluck(Closure $callback): array
    {
        $result = [];

        $packages = config('metafox.packages');

        if (!empty($packages)) {
            foreach ($packages as $package) {
                $x = $callback($package);
                if (null !== $x) {
                    $result[$package['name']] = $x;
                }
            }
        }

        return $result;
    }

    /**
     * @param string $name
     *
     * @return string[]
     */
    public function getResourceNames(string $name): array
    {
        $resources = [];
        $response  = PackageManager::discoverSettingsPackageKey('getItemTypes');

        if (array_key_exists($name, $response)) {
            $resources = $response[$name];
        }

        return $resources;
    }

    /**
     * Get the listener class name of a package.
     *
     * @param string $name
     *
     * @return string
     */
    public function getListenerClass(string $name): string
    {
        return sprintf('%s\\Listeners\\PackageSettingListener', static::getNamespace($name));
    }

    /**
     * Resolve listener instance of a package.
     *
     * @param string $name Package name. Example: metafox/core
     *
     * @return BasePackageSettingListener|null
     */
    public function getListener(string $name): ?BasePackageSettingListener
    {
        $class = self::getListenerClass($name);

        if (!class_exists($class, true)) {
            Log::channel('installation')->debug('Failed loading ' . $class);

            return null;
        }

        /* @var BasePackageSettingListener $listener */
        return resolve($class);
    }

    /**
     * Get package name by an alias name.
     *
     * @param string $alias
     *
     * @return string
     */
    public function getByAlias(string $alias): string
    {
        $package = Arr::first(config('metafox.packages'), function ($item) use ($alias) {
            return $item['alias'] === $alias;
        });

        return $package ? $package['name'] : '';
    }

    /**
     * Create package name.
     *
     * @param  string $vendorName
     * @param  string $appName
     * @return string
     */
    public function normalizePackageName(string $vendorName, string $appName): string
    {
        return Str::lower($vendorName) . '/' . Str::kebab($appName, '-');
    }

    /**
     * @param string       $package Package name to export file
     * @param string       $path    Path within package directory. Example: resources/lang/en.php
     * @param array<mixed> $data    Data to export. Example [[item=>1]]
     */
    public function exportToFilesystem(string $package, string $path, array $data): string
    {
        $dir = self::getPath($package);

        if (!$dir) {
            throw new \InvalidArgumentException(sprintf('Package not found %s', $package));
        }

        $baseFile = implode(DIRECTORY_SEPARATOR, [$dir, $path]);
        $filename = base_path() . DIRECTORY_SEPARATOR . $baseFile;

        // ensure dir is exists
        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }

        if (file_exists($filename) && !is_writable($filename)) {
            throw new \RuntimeException('Could not write to ' . $filename);
        }

        export_to_file($filename, $data);

        return $baseFile;
    }

    /**
     * @param  string     $package  Package name. Example: metafox/blog
     * @param  string     $filename : related file name in under package. etc: resources/lang/en.php
     * @param  bool       $silent
     * @return array|null
     */
    public function readFile(string $package, string $filename, bool $silent = false): ?array
    {
        try {
            $path = app()->basePath(implode(
                DIRECTORY_SEPARATOR,
                [self::getPath($package), $filename]
            ));

            if (!app('files')->exists($path)) {
                return null;
            }

            $data = app('files')->getRequire($path);

            if (!is_array($data)) {
                return null;
            }

            return $data;
        } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $exception) {
            Log::channel('installation')->debug($exception->getMessage());
            // skip error
        }

        return null;
    }

    public function with(Closure $callback)
    {
        $data = config('metafox.packages', []);
        foreach ($data as $name => $info) {
            $callback($name, $info);
        }
    }

    public function withActivePackages(Closure $callback): void
    {
        $data = config('metafox.packages', []);

        $aliases = config('app.mfox_installed') ?
            resolve(PackageRepository::class)
                ->getActivePackageAliases() : null;

        foreach ($data as $info) {
            if ($aliases && !$info['core'] && !in_array($info['alias'], $aliases)) {
                continue;
            }
            $callback($info);
        }
    }

    public function withInstalledPackages(Closure $callback): void
    {
        $data = config('metafox.packages', []);

        $installedPackages = resolve(PackageRepositoryInterface::class)
                ->getInstalledPackageAliases();

        foreach ($data as $name => $info) {
            if ($installedPackages && !$info['core'] && !in_array($info['alias'], $installedPackages)) {
                continue;
            }

            $callback($name, $info);
        }
    }

    public function updateEnvironmentFile(array $values): void
    {
        $envFile = base_path('.env');
        if (!file_exists($envFile) || !is_writable($envFile)) {
            throw new \RuntimeException('File .env is not exists or not wriable.');
        }

        $content = mf_get_contents($envFile);

        foreach ($values as $name => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_numeric($value)) {
                $value = sprintf('%s', $value);
            } elseif (null === $value) {
                $value = sprintf('%s', 'null');
            } elseif (!empty($value)) {
                $value = sprintf('"%s"', $value);
            } else {
                $value = '';
            }

            $pattern = sprintf('/^%s *= *([^\n]*)$/m', $name);
            $need    = $name . '=' . $value;

            if (preg_match($pattern, $content)) {
                $content = preg_replace(
                    $pattern,
                    $need,
                    $content
                );
            } else {
                $content = $content . PHP_EOL . $need;
            }
        }

        // update content file.
        @file_put_contents($envFile, $content);
    }

    /**
     * @param string $name
     *
     * @return array<mixed>
     */
    public function discoverSettingsPackageKey(string $name): array
    {
        return localCacheStore()->rememberForever(sprintf('packages.%s', $name), function () use ($name) {
            $result    = [];
            $listeners = $this->getListeners();

            foreach ($listeners as $package => $listener) {
                $data = $listener->handle($name);
                if (!empty($data)) {
                    $result[$package] = $data;
                }
            }

            return $result;
        });
    }

    /**
     * This method is called to assign scheduler. It should be run in console only.
     *
     *
     * @param Schedule $schedule
     *
     * @see \App\Console\Kernel::schedule()
     */
    public function registerApplicationSchedule(Schedule $schedule): void
    {
        /*
         * This method should be call on console only.
         * remove this method to others, because we should not reduce load time.
         * Check its carefully because it's need instance all listeners agains.
         */
        foreach ($this->getListeners() as $listener) {
            if (method_exists($listener, 'registerApplicationSchedule')) {
                $listener->registerApplicationSchedule($schedule);
            }
        }
    }
}
