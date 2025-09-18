<?php

namespace MetaFox\Quiz\Http\Resources\v1\Answer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Quiz\Models\Answer as Model;

/**
 * Class AnswerItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AnswerItem extends JsonResource
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
            'answer'        => ban_word()->clean($this->resource->answer),
            'is_correct'    => (bool) ($this->resource->is_correct),
            'ordering'      => $this->resource->ordering,
        ];
    }
}
