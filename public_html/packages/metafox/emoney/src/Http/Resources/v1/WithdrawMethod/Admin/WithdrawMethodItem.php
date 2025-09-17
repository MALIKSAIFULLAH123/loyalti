<?php

namespace MetaFox\EMoney\Http\Resources\v1\WithdrawMethod\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\EMoney\Models\WithdrawMethod as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class WithdrawMethodItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class WithdrawMethodItem extends JsonResource
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
            'id'        => $this->resource->service,
            'title'     => $this->resource->title,
            'is_active' => $this->resource->is_active,
        ];
    }
}
