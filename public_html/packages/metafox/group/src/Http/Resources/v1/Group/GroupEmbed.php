<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use Illuminate\Http\Request;
use MetaFox\Group\Models\Group as Model;

/*
|--------------------------------------------------------------------------
| Resource Embed
|--------------------------------------------------------------------------
|
| Resource embed is used when you want attach this resource as embed content of
| activity feed, notification, ....
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
*/

/**
 * Class GroupEmbed.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class GroupEmbed extends GroupItem
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->id,
            'module_name'   => $this->resource->moduleName(),
            'resource_name' => $this->resource->entityType(),
            'statistic'     => $this->getStatistic(),
            'extra'         => $this->getGroupExtra(),
        ];
    }
}
