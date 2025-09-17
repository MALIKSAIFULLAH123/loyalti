<?php

namespace MetaFox\Platform\Http\Resources\v1\Category;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

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
 * Class CategoryPropertiesSEO.
 */
class CategoryPropertiesSEO extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param $request
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        if (!$this->resource instanceof Model) {
            return $this->resourcesDefault();
        }

        return [
            'id'            => $this->resource?->entityId(),
            'resource_name' => $this->resource?->entityType(),
            'name'          => $this->resource?->name,
            'name_url'      => $this->resource?->name_url,
            'link'          => $this->resource?->toLink(),
            'url'           => $this->resource?->toUrl(),
        ];
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'            => null,
            'resource_name' => null,
            'name'          => null,
            'name_url'      => null,
            'link'          => null,
            'url'           => null,
        ];
    }
}
