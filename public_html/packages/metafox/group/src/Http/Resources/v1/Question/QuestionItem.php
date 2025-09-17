<?php

namespace MetaFox\Group\Http\Resources\v1\Question;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Question as Model;

/**
 * Class QuestionItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class QuestionItem extends JsonResource
{
    public bool $preserveKeys = true;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'group',
            'resource_name' => $this->resource->entityType(),
            'question'      => ban_word()->clean($this->resource->question),
            'type'          => $this->resource->type_id,
            'group_id'      => $this->resource->group->entityId(),
            'options'       => $this->resource->questionFieldsForFE,
        ];
    }
}
