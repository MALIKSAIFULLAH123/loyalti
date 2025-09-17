<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform;

use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * Class PackageManager.
 * @method static bool                            checkActive(string $packageName)
 * @method static bool                            isCore(string $packageName)
 * @method static array                           getInfo(string $packageName)
 * @method static string                          getName(string $packageName)
 * @method static string                          getTitle(string $packageName)
 * @method static string                          getNamespace(string $packageName)
 * @method static string                          getAlias(string $packageName)
 * @method static string                          getFrontendAlias(string $packageName)
 * @method static string                          getAliasFor(string $packageName, ?string $for = null)
 * @method static string                          getPath(string $packageName)
 * @method static string|null                     getComposerJsonPath(string $packageName)
 * @method static string|null                     getComposerJson(string $packageName)
 * @method static string|null                     getBasePath(string $packageName)
 * @method static string                          getMigrationPath(string $packageName)
 * @method static string                          getConfigPath(string $packageName)
 * @method static string                          getAssetPath(string $packageName)
 * @method static array                           getConfig(string $packageName)
 * @method static array                           getMigrations(string $packageName)
 * @method static string[]                        getPackageNames(string $packageName)
 * @method static string                          getNameStudly(string $packageName)
 * @method static string[]                        getMasterSeederClasses(string $packageName)
 * @method static string|null                     getSeeder(string $packageName)
 * @method static array                           pluck()
 * @method static string[]                        getResourceNames(string $packageName)
 * @method static string                          getListenerClass(string $packageName)
 * @method static BasePackageSettingListener|null getListener(string $packageName)
 * @method static string                          getByAlias(string $aliasName)
 * @method static string                          normalizePackageName(string $vendorName, string $appName)
 * @method static string                          exportToFilesystem(string $package, string $path, array $data)
 * @method static array|null                      readFile(string $package, string $filename)
 * @method static void                            with(\Closure $callback)
 * @method static void                            withActivePackages(\Closure $callback)
 * @method static void                            withInstalledPackages(\Closure $callback)
 * @method static void                            updateEnvironmentFile(array $values)
 * @method static array<string, string>           getDatabaseMigrationsFrom()
 * @method static void                            registerApplicationSchedule()
 * @method static array                           discoverSettings(string $settingName)
 * @method static PackageManagerImpl              instance()
 * @method static array<mixed>                    discoverSettingsPackageKey(string $settingName)
 * @method static array                           getEvents()
 * @method static string                          getAliasForEntityType(string $entityType)
 * @see PackageManagerImpl
 */
class PackageManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PackageManagerImpl::class;
    }
}
