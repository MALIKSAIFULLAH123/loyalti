<?php

namespace MetaFox\Importer\Http\Resources\v1\Bundle\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Importer\Models\Bundle as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class BundleDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class BundleDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
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
