<?php

namespace MetaFox\Page\Http\Resources\v1\PageClaim\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Models\PageClaim as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class PageClaimDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class PageClaimDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'module_name'   => '',
            'resource_name' => $this->entityType(),
        ];
    }
}
