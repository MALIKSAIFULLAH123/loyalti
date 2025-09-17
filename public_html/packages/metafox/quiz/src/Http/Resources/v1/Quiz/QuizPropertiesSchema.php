<?php

namespace MetaFox\Quiz\Http\Resources\v1\Quiz;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz as Model;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;

/*
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
|
*/

/**
 * Class QuizPropertiesSchema.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuizPropertiesSchema extends JsonResource
{
    use HasHashtagTextTrait;

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
        $user           = new UserPropertiesSchema($this->resource?->user);
        $userProperties = Arr::dot($user->toArray($request), 'user_');
        $userProperties = Arr::undot($userProperties);

        if (!$this->resource instanceof Model) {
            return array_merge($this->resourcesDefault(), $userProperties);
        }

        $quizText = $this->resource->quizText;

        $shortDescription = $text = '';
        if ($quizText) {
            $shortDescription = parse_output()->getDescription($quizText->text_parsed);
            $text             = $this->getTransformContent($this->resource->quizText->text_parsed);
            $text             = parse_output()->parseItemDescription($text);
        }

        $reactItem = $this->resource->reactItem();

        return array_merge([
            'id'                  => $this->resource->entityId(),
            'title'               => ban_word()->clean($this->resource->title),
            'description'         => $shortDescription,
            'text'                => $text,
            'image'               => $this->resource->image,
            'creation_date'       => Carbon::parse($this->resource->created_at)->format('c'),
            'modification_date'   => Carbon::parse($this->resource->updated_at)->format('c'),
            'link'                => $this->resource->toLink(),
            'url'                 => $this->resource->toUrl(),
            'total_like'          => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_view'          => $this->resource->total_view,
            'total_play'          => $this->resource->total_play,
            'structured_has_part' => $this->buildHasPartQuestions(),
        ], $userProperties);
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                  => null,
            'title'               => null,
            'description'         => null,
            'text'                => null,
            'image'               => null,
            'creation_date'       => null,
            'modification_date'   => null,
            'link'                => null,
            'url'                 => null,
            'total_like'          => null,
            'total_view'          => null,
            'total_play'          => null,
            'structured_has_part' => null,
        ];
    }

    protected function buildHasPartQuestions(): array
    {
        $results = [];
        $this->resource->questions->each(function (Question $question) use (&$results) {
            $results[] = [
                '@type'           => 'Question',
                'eduQuestionType' => 'Multiple choice',
                'text'            => ban_word()->clean($question->question),
                'suggestedAnswer' => $this->buildSuggestedAnswer($question),
            ];
        });

        return $results;
    }

    protected function buildSuggestedAnswer(Question $question): array
    {
        $results = [];
        $question->answers->each(function (Answer $answer) use (&$results) {
            $results[] = [
                '@type'    => 'Answer',
                'position' => $answer->ordering,
                'text'     => ban_word()->clean($answer->answer),
            ];
        });

        return $results;
    }
}
