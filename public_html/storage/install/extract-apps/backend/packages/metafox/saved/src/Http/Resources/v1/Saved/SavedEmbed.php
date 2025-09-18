<?php

namespace MetaFox\Saved\Http\Resources\v1\Saved;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Saved\Models\Saved as Model;

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
 * Class SavedEmbed.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SavedEmbed extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request       $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => '',
            'resource_name' => $this->resource->entityType(),
        ];
    }
}
