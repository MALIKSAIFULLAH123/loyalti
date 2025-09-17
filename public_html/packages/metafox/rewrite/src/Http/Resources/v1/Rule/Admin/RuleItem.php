<?php

namespace MetaFox\Rewrite\Http\Resources\v1\Rule\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Rewrite\Models\Rule as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class RuleItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class RuleItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'             => $this->resource->id,
            'module_name'    => 'core',
            'resource_name'  => $this->resource->entityType(),
            'from_path'      => $this->resource->from_path,
            'to_path'        => $this->resource->to_path,
            'to_mobile_path' => $this->resource->to_mobile_path,
        ];
    }
}
