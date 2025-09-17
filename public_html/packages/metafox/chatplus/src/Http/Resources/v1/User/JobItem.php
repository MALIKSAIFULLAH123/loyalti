<?php

namespace MetaFox\ChatPlus\Http\Resources\v1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\ChatPlus\Models\Job;

/**
 * Class JobItem.
 * @property Job $resource
 */
class JobItem extends JsonResource
{
    /**
     * @param  Request  $request
     *
     * @return array<mixed>
     */
    public function toArray($request)
    {
        // @todo check role of user
        return [
            'id'   => $this->resource->id,
            'name' => $this->resource->name,
            'data' => $this->resource->data,
        ];
    }
}
