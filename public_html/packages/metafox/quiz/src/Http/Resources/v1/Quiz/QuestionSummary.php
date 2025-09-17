<?php

namespace MetaFox\Quiz\Http\Resources\v1\Quiz;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Quiz\Models\Question as Model;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class QuizDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuestionSummary extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        return [
            'id'             => $this->resource->entityId(),
            'question_title' => ban_word()->clean($this->resource->question),
            'answers'        => $this->processAnswer($this->resource->answers),
            'module_name'    => 'quiz',
            'resource_name'  => 'quiz_view_summary',
        ];
    }

    protected function processAnswer($answers): array
    {
        $questionTotalPlay = 0;

        foreach ($answers as $answer) {
            $questionTotalPlay += $answer->total_play;
        }

        $processedAnswers = [];

        foreach ($answers as $answer) {
            $answer = $answer->toArray();

            $answer['percent'] = $answer['total_play'] . '/' . $questionTotalPlay;

            $answer['percent_value'] = $questionTotalPlay > 0 ? round(($answer['total_play'] / $questionTotalPlay) * 100, 2) : 0;

            if (Settings::get('quiz.show_success_as_percentage_in_result', true)) {
                $answer['percent'] = ($answer['total_play'] == 0 ? 0 : round(($answer['total_play'] / $questionTotalPlay) * 100, 2)) . '%';
            }

            $answerText = Arr::get($answer, 'answer');
            if (!empty($answerText)) {
                Arr::set($answer, 'answer', ban_word()->clean($answerText));
            }

            $processedAnswers[] = $answer;
        }

        return $processedAnswers;
    }
}
