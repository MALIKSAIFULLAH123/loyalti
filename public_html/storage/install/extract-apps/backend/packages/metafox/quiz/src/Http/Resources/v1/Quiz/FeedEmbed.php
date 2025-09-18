<?php

namespace MetaFox\Quiz\Http\Resources\v1\Quiz;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Quiz\Http\Resources\v1\Question\QuestionItemCollection;
use MetaFox\Quiz\Http\Resources\v1\Result\ResultDetail;
use MetaFox\Quiz\Http\Resources\v1\Result\ResultItemCollection;
use MetaFox\Quiz\Models\Quiz as Model;
use MetaFox\Quiz\Models\Result;
use MetaFox\Quiz\Support\ResourcePermission;

/*
|--------------------------------------------------------------------------
| Resource Embed
|--------------------------------------------------------------------------
|
| Resource embed is used when you want attach this resource as embed content of
| activity feed, notification, ....
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
*/

/**
 * Class QuizEmbed.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FeedEmbed extends JsonResource
{
    use HasExtra;

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $shortDescription = '';

        $quizText         = $this->resource->quizText;

        if ($quizText) {
            $shortDescription = parse_output()->getDescription($quizText->text_parsed);
        }

        $postOnOther   = $this->resource->userId() != $this->resource->ownerId();

        $ownerResource = null;

        if ($postOnOther && null !== $this->resource->ownerEntity) {
            $ownerResource = ResourceGate::user($this->resource->ownerEntity);
        }

        return [
            'id'                => $this->resource->entityId(),
            'resource_name'     => $this->resource->entityType(),
            'module_name'       => $this->resource->entityType(),
            'title'             => ban_word()->clean($this->resource->title),
            'description'       => $shortDescription,
            'questions'         => new QuestionItemCollection($this->resource->questions),
            'image'             => $this->resource->images,
            'privacy'           => $this->resource->privacy,
            'link'              => $this->resource->toLink(),
            'is_sponsor'        => $this->resource->is_sponsor,
            'is_featured'       => $this->resource->is_featured,
            'statistic'         => $this->getStatistic(),
            'attachments'       => ResourceGate::items($this->resource->attachments, false),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'parent_user'       => $ownerResource,
            'info'              => 'added_a_quiz',
            'creation_date'     => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
            'results'           => $this->getUserResults(user()),
            'member_results'    => new ResultItemCollection($this->resource->results),
            'extra'             => $this->getCustomExtra(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        return [
            'total_like'       => $this->resource->total_like,
            'total_play'       => $this->resource->total_play,
            'total_view'       => $this->resource->total_view,
            'total_comment'    => $this->resource->total_comment,
            'total_attachment' => $this->resource->total_attachment,
            'total_share'      => $this->resource->total_share,
        ];
    }

    public function getUserResults(User $context): ResultDetail
    {
        $userResults = $this->resource->results->filter(function (Result $result) use ($context) {
            if (!$result->user instanceof User) {
                return false;
            }

            return $result->user->entityId() == $context->entityId();
        })->first();

        return new ResultDetail($userResults);
    }

    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    protected function getCustomExtra(): array
    {
        $extras = $this->getExtra();

        $context = user();

        return array_merge($extras, [
            ResourcePermission::CAN_PLAY     => $context->can('play', [Model::class, $this->resource]),
            'can_moderate'                   => $context->hasPermissionTo('quiz.moderate'),
            'can_view_results_before_answer' => $context->hasPermissionTo('quiz.view_answers'),
        ]);
    }
}
