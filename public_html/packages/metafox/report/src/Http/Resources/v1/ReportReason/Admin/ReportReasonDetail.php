<?php

namespace MetaFox\Report\Http\Resources\v1\ReportReason\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Report\Models\ReportReason as Model;

/**
 * |--------------------------------------------------------------------------
 * | Resource Detail
 * |--------------------------------------------------------------------------
 * | stub: /packages/resources/detail.stub
 * | @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview.
 **/

/**
 * Class ReportReasonDetail.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ReportReasonDetail extends JsonResource
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
            'id'            => $this->resource->id,
            'name'          => $this->resource->name,
            'ordering'      => $this->resource->ordering,
            'creation_date' => $this->resource->created_at,
            'is_default'    => $this->resource->is_default,
        ];
    }
}
