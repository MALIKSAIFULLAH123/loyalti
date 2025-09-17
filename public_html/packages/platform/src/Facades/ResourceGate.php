<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Facades;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\ApiResourceManager;

/**
 * Class ResourceGate.
 * @method static void              setVersion($version)
 * @method static string            getVersion()
 * @method static string            getMajorVersion()
 * @method static JsonResource|null asResource($model, $variant, mixed $checkPrivacy = 'view')
 * @method static array|null        asJson($model, $variant, mixed $checkPrivacy = 'view')
 * @method static array|null        item($model, mixed $checkPrivacy = 'view')
 * @method static array|null        items(\ArrayAccess $items, mixed $checkPrivacy = 'view')
 * @method static array|null        embeds(\ArrayAccess $items, mixed $checkPrivacy = 'view')
 * @method static array|null        detail($model, mixed $checkPrivacy = 'view')
 * @method static array|null        embed($model, mixed $checkPrivacy = 'view')
 * @method static JsonResource|null asItem($model, mixed $checkPrivacy = 'view')
 * @method static JsonResource|null asEmbed($model, mixed $checkPrivacy = 'view')
 * @method static JsonResource|null asDetail($model, mixed $checkPrivacy = 'view')
 * @method static JsonResource|null toItem(mixed $itemType, mixed $itemId, mixed $checkPrivacy = 'view')
 * @method static JsonResource|null toResource(mixed $variant, mixed $itemType, mixed $itemId, mixed $checkPrivacy =   'view')
 * @method static presentAs
 * @method static mixed       getItem(mixed $itemType, mixed $itemId)
 * @method static string|null pickNearestVersion(array $versions)
 * @method static array       user($model)
 * @method static array       transactionUser($model)
 * @link \MetaFox\Platform\ApiResourceManager::setVersion
 */
class ResourceGate extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ApiResourceManager::class;
    }
}
