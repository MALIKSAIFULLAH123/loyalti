<?php

namespace MetaFox\User\Http\Resources\v1\CancelFeedback\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Models\CancelFeedback as Model;

/**
 * Class CancelFeedbackItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CancelFeedbackDetail extends JsonResource
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
