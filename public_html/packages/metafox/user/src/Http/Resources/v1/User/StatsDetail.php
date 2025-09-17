<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

class StatsDetail extends JsonResource
{
    public function toArray($request)
    {
        $context = user();

        $statistics = resolve(UserRepositoryInterface::class)->getItemExtraStatistics($context, $this->resource, $request->get('item_type'), $request->get('item_id'));

        return [
            'id'               => $this->resource->entityId(),
            'resource_name'    => 'user',
            'module_name'      => 'user',
            'extra_statistics' => $statistics,
        ];
    }
}
