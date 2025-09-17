<?php

namespace MetaFox\Poll\Http\Resources\v1\Answer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Poll\Http\Resources\v1\Result\ResultItemCollection;
use MetaFox\Poll\Models\Answer as Model;

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
        $votes = $this->resource->votes;

        return [
            'id'                => $this->resource->entityId(),
            'resource_name'     => $this->resource->entityType(),
            'answer'            => ban_word()->clean($this->resource->answer),
            'total_votes'       => $this->resource->total_vote,
            'ordering'          => $this->resource->ordering,
            'voted'             => $this->resource->voted,
            'vote_percentage'   => $this->resource->percentage,
            'some_votes'        => new ResultItemCollection($votes),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'privacy'           => 0, //@todo discuss with LamTB
        ];
    }
}
