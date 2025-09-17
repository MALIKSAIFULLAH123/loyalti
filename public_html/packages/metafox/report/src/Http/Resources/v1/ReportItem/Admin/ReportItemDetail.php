<?php

namespace MetaFox\Report\Http\Resources\v1\ReportItem\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Report\Models\ReportItem as Model;

/**
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
 **/

/**
 * Class ReportItemDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ReportItemDetail extends JsonResource
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
