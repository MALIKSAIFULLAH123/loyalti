<?php

namespace MetaFox\Event\Http\Resources\v1\Event;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Event\Repositories\EventRepositoryInterface;

class EventStatDetail extends JsonResource
{
    public function toArray($request)
    {
        $repository = resolve(EventRepositoryInterface::class);

        $context = user();

        return [
            'id'               => $this->resource->entityId(),
            'resource_name'    => $this->resource->entityType(),
            'module_name'      => 'event',
            'extra_statistics' => $repository->getExtraStatistics($context, $this->resource),
        ];
    }
}
