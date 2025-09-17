<?php

namespace MetaFox\Quiz\Http\Resources\v1\Question;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Quiz\Http\Resources\v1\Answer\AnswerItemCollection;
use MetaFox\Quiz\Models\Question as Model;

/**
 * Class QuestionItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class QuestionItem extends JsonResource
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
            'question'      => ban_word()->clean($this->resource->question),
            'answers'       => new AnswerItemCollection($this->resource->answers),
            'ordering'      => $this->resource->ordering,
        ];
    }
}
