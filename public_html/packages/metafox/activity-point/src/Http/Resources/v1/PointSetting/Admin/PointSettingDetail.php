<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointSetting\Admin;

use Illuminate\Http\Request;
use MetaFox\ActivityPoint\Models\PointSetting as Model;

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
class PointSettingDetail extends PointSettingItem
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
