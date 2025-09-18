<?php

namespace MetaFox\Quiz\Http\Resources\v1\ResultDetail;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\ResultDetail as Model;

/**
 * Class ResultDetailItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ResultDetailItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $correctAnswer = $this->resource->question->answers->filter(function (Answer $item) {
            return $item->is_correct;
        })->first();

        return [
            'question'            => ban_word()->clean($this->resource->question->question),
            'question_id'         => $this->resource->question->entityId(),
            'user_answer_text'    => ban_word()->clean($this->resource?->answer?->answer),
            'user_answer_id'      => $this->resource?->answer?->entityId(),
            'user_answer_date'    => $this->resource->result->created_at,
            'correct_answer_text' => empty($correctAnswer) ? null : ban_word()->clean($correctAnswer->answer),
            'correct_answer_id'   => empty($correctAnswer) ? null : $correctAnswer->entityId(),
        ];
    }
}
