<?php

namespace MetaFox\AntiSpamQuestion\Http\Resources\v1\Question\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\AntiSpamQuestion\Models\Question as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class QuestionItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class QuestionItem extends JsonResource
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
            'id'                => $this->resource->entityId(),
            'question'          => $this->resource->toTitle(),
            'is_active'         => $this->resource->is_active,
            'is_case_sensitive' => $this->resource->is_case_sensitive,
            'created_at'        => $this->resource->created_at,
            'image'             => [
                'file_type' => 'image/*',
                'url'       => $this->resource->image,
            ],
            'links'             => [
                'editItem' => '/antispamquestion/question/edit/' . $this->resource->entityId(),
            ],
        ];
    }
}
