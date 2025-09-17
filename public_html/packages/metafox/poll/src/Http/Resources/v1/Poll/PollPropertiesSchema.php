<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll as Model;
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
 * Class PollPropertiesSchema.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PollPropertiesSchema extends JsonResource
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

        $pollText = $this->resource->pollText;

        $description = $text = '';
        if (null !== $pollText) {
            $description = parse_output()->getDescription($pollText->text_parsed);
            $text        = $this->getTransformContent($pollText->text_parsed);
            $text        = parse_output()->parseItemDescription($text);
        }

        $reactItem = $this->resource->reactItem();

        return array_merge([
            'id'                => $this->resource->entityId(),
            'question'          => ban_word()->clean($this->resource->question),
            'description'       => $description,
            'text'              => $text,
            'close_time'        => $this->resource->closed_at,
            'image'             => $this->resource->image,
            'creation_date'     => Carbon::parse($this->resource->created_at)->format('c'),
            'modification_date' => Carbon::parse($this->resource->updated_at)->format('c'),
            'answer_count'      => $this->resource->answers->count(),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'total_like'        => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_view'        => $this->resource->total_view,
            'total_vote'        => $this->resource->total_vote,
            'suggested_answer'  => $this->buildSuggestedAnswer(),
        ], $userProperties);
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                => null,
            'question'          => null,
            'description'       => null,
            'text'              => null,
            'close_time'        => null,
            'image'             => null,
            'creation_date'     => null,
            'modification_date' => null,
            'answer_count'      => null,
            'link'              => null,
            'url'               => null,
            'total_like'        => null,
            'total_view'        => null,
            'total_vote'        => null,
            'suggested_answer'  => null,
        ];
    }

    protected function buildSuggestedAnswer(): array
    {
        $results = [];
        $this->resource->answers->each(function (Answer $answer) use (&$results) {
            $results[] = [
                '@type'       => 'Answer',
                'position'    => $answer->ordering,
                'text'        => ban_word()->clean($answer->answer),
                'upvoteCount' => $answer->total_vote,
            ];
        });

        return $results;
    }
}
