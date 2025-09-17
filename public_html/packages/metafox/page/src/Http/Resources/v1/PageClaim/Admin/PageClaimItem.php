<?php

namespace MetaFox\Page\Http\Resources\v1\PageClaim\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Models\PageClaim as Model;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PageClaimItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PageClaimItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $message = $this->resource->message ? parse_output()->handleNewLineTag($this->resource->message) : null;

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'page',
            'resource_name' => $this->resource->entityType(),
            'page'          => ResourceGate::asEmbed($this->resource->page, false),
            'user'          => ResourceGate::user($this->resource->userEntity),
            'description'   => $message,
        ];
    }
}
