<?php

namespace MetaFox\Layout\Http\Resources\v1\Build\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Layout\Models\Build as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class BuildItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class BuildItem extends JsonResource
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
            'job_id'        => $this->job_id,
            'reason'        => $this->reason,
            'bundle_url'    => $this->bundle_url,
            'log_url'       => $this->log_url,
            'bundle_status' => $this->bundle_status,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
