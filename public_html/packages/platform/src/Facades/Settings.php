<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Core\Repositories\Eloquent\SiteSettingRepository;
use MetaFox\Platform\Contracts\SiteSettingRepositoryInterface;

/**
 * Class Settings.
 *
 * @method static bool   has(string $key)
 * @method static mixed  get(string $key, $default = null)
 * @method static array  save(array $values)
 * @method static array  saveAndCollectModifiedSettings(array $values, ?array &$privateSettings = [])
 * @method static bool   setupPackageSettings(string $module, array $settings)
 * @method static bool   destroy(string $module, ?array $names)
 * @method static bool   updateSetting(string $module, string $name, ?string $configName, ?string $envVar, mixed $value, string $type, bool $public, bool $auto)
 * @method static bool   createSetting(string $module, string $name, ?string $configName, ?string $envVar, mixed $value, string $type, bool $public, bool $auto)
 * @method static bool   reset(string $module, ?array $names)
 * @method static array  getSiteSettings(string $for, bool $loadFromDriver)
 * @method static void   bootingKernelConfigs()
 * @method static void   mockValues(array $values)
 * @method static int    versionId()
 * @method static void   refresh()
 * @method static string updatedAt()
 * @method static array  keys()
 * @link SiteSettingRepository
 */
class Settings extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SiteSettingRepositoryInterface::class;
    }
}
