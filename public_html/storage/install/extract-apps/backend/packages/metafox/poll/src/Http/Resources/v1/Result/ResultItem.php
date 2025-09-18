<?php

namespace MetaFox\Poll\Http\Resources\v1\Result;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Poll\Models\Result as Model;

/**
 * Class ResultItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ResultItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->entityId(),
            'resource_name' => $this->resource->entityType(),
            'user'          => ResourceGate::user($this->resource->userEntity),
            'creation_date' => $this->resource->created_at,
            'statistic'     => $this->getStatistic(),
        ];
    }

    /**
     * @return array
     */
    private function getStatistic(): array
    {
        return [
            'total_mutual' => $this->resource->getTotalMutual(),
        ];
    }
}
