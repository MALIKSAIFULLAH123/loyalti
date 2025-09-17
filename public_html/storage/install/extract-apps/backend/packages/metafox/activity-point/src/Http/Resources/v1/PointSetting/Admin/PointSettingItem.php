<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointSetting\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\ActivityPoint\Models\PointSetting as Model;
use MetaFox\App\Models\Package;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PointSettingItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PointSettingItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $module     = resolve('core.packages')->getPackageByName($this->resource->package_id);
        $moduleName = $module instanceof Package ? $module->title : __p('core::phrase.system');

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'activitypoint',
            'resource_name' => $this->resource->entityType(),
            'module_id'     => $moduleName,
            'package_id'    => $this->resource->package_id,
            'name'          => $this->resource->name,
            'action'        => $this->resource->action,
            'description'   => $this->resource->description,
            'is_active'     => $this->resource->is_active,
            'points'        => $this->resource->points,
            'max_earned'    => $this->resource->max_earned,
            'period'        => $this->resource->period,
            'role'          => $this->resource->role?->name,
        ];
    }
}
