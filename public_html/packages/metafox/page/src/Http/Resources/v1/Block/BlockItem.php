<?php

namespace MetaFox\Page\Http\Resources\v1\Block;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Models\Block as Model;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class BlockItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class BlockItem extends JsonResource
{
    use HasExtra;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->id,
            'module_name'   => 'page',
            'resource_name' => $this->resource->entityType(),
            'page_id'       => $this->resource->page_id,
            'user_id'       => $this->resource->user_id,
            'owner_id'      => $this->resource->owner_id,
            'user'          => ResourceGate::asDetail($this->resource->user),
            'owner'         => ResourceGate::asDetail($this->resource->ownerEntity),
        ];
    }
}
