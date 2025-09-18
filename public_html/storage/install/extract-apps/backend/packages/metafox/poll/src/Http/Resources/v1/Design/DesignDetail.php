<?php

namespace MetaFox\Poll\Http\Resources\v1\Design;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Poll\Models\Design as Model;

/*
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
|
*/

/**
 * Class DesignDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class DesignDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request       $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->entityId(),
            'resource_name' => $this->resource->entityType(),
            'background'    => !empty($this->resource->background) ? $this->resource->background : '',
            'percentage'    => !empty($this->resource->percentage) ? $this->resource->percentage : '',
            'border'        => !empty($this->resource->border) ? $this->resource->border : '',
        ];
    }
}
