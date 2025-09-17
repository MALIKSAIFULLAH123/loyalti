<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Http\Resources\v1\Error;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

/**
 * --------------------------------------------------------------------------
 * Support returning error instead of complete entity data.
 * --------------------------------------------------------------------------.
 *
 * @link \MetaFox\Core\Listeners\PackageSettingListener::getHttpResourceVersions();
 */

/**
 * Class Forbidden.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @driverName  forbidden.embed
 */
class Forbidden extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->moduleName(),
            'resource_name' => $this->resource->entityType(),
            'title'         => __p('core::phrase.item_privacy_error.title'),
            'message'       => __p('core::phrase.item_privacy_error.forbidden'),
            'error'         => Response::HTTP_FORBIDDEN,
        ];
    }
}
