<?php

namespace MetaFox\User\Http\Resources\v1\User\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Models\User as Model;

/**
 * Class UserItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'             => $this->resource->entityId(),
            'module_name'    => 'user',
            'resource_name'  => $this->resource->entityType(),
            'full_name'      => $this->resource->full_name,
            'display_name'   => $this->resource->display_name,
            'role_name'      => $this->resource->transformRole(),
            'email'          => $this->resource->email,
            'created_at'     => (new Carbon($this->resource->created_at))->toDateTimeString(),
            'is_approved'    => $this->resource->isApproved(),
            'approve_status' => $this->resource->approve_status,
        ];
    }
}
