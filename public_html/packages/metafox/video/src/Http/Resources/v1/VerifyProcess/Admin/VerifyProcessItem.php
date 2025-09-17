<?php

namespace MetaFox\Video\Http\Resources\v1\VerifyProcess\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Video\Models\VerifyProcess as Model;
use MetaFox\Video\Support\Facade\Video;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class VerifyProcessItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class VerifyProcessItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'             => $this->resource->entityId(),
            'user'           => ResourceGate::user($this->resource->userEntity),
            'module_name'    => 'video',
            'resource_name'  => $this->resource->entityType(),
            'total_verified' => $this->resource->total_verified,
            'total_videos'   => $this->resource->total_videos,
            'status'         => $this->resource->status,
            'status_text'    => Video::getStatusVerifyProcessTexts($this->resource),
            'process_text'   => $this->resource->process_text,
            'created_at'     => $this->resource->created_at,
        ];
    }
}
