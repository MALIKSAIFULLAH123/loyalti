<?php

namespace MetaFox\Queue\Http\Resources\v1\FailedJob\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Queue\Models\FailedJob as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class FailedJobItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class FailedJobItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $obj = $this->resource;

        return [
            'id'         => $obj->id,
            'uuid'       => $obj->uuid,
            'exception'  => $obj->exception,
            'failed_at'  => $obj->failed_at,
            'queue'      => $obj->queue,
            'connection' => $obj->connection,
            'links'      => [
                'retryItem'  => '/queue/failed_job/' . $this->resource->uuid,
                'deleteItem' => '/queue/failed_job/' . $this->resource->uuid,
            ],
        ];
    }
}
