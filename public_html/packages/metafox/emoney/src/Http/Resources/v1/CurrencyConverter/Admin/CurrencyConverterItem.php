<?php

namespace MetaFox\EMoney\Http\Resources\v1\CurrencyConverter\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\EMoney\Models\CurrencyConverter as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class CurrencyConverterItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class CurrencyConverterItem extends JsonResource
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
            'id'         => $this->resource->service,
            'title'      => $this->resource->title,
            'link'       => $this->resource->link,
            'is_default' => $this->resource->is_default ? null : false,
        ];
    }
}
