<?php

namespace MetaFox\Quiz\Http\Resources\v1\Quiz;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\Quiz\Http\Resources\v1\Question\QuestionItemCollection;
use MetaFox\Quiz\Http\Resources\v1\Result\ResultDetail;
use MetaFox\Quiz\Http\Resources\v1\Result\ResultItemCollection;
use MetaFox\Quiz\Models\Quiz as Model;
use MetaFox\Quiz\Models\Result;
use MetaFox\Quiz\Support\ResourcePermission;

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
 * Class QuizDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuizDetail extends JsonResource
{
    use HasExtra;
    use HasStatistic;
    use HasFeedParam;
    use IsFriendTrait;
    use IsLikedTrait;
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
        $context  = user();
        $quizText = $this->resource->quizText;

        $shortDescription = $text = '';
        if ($quizText) {
            $shortDescription = parse_output()->getDescription($quizText->text_parsed);
            $text             = $this->getTransformContent($this->resource->quizText->text_parsed);
            $text             = parse_output()->parseItemDescription($text);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'title'             => ban_word()->clean($this->resource->title),
            'description'       => $shortDescription,
            'questions'         => new QuestionItemCollection($this->resource->questions),
            'is_sponsor'        => (bool) $this->resource->is_sponsor,
            'is_featured'       => (bool) $this->resource->is_featured,
            'is_pending'        => !$this->resource->is_approved,
            'is_liked'          => $this->isLike($context, $this->resource),
            'is_friend'         => $this->isFriend($context, $this->resource->user),
            'is_saved'          => PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]),
            'is_owner'          => user()->entityId() == $this->resource->userId(),
            'text'              => $text,
            'image'             => $this->resource->images,
            'view_id'           => $this->resource->view_id,
            'module_id'         => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'           => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'statistic'         => $this->getStatistic(),
            'privacy'           => $this->resource->privacy,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'owner_type_name'   => __p_type_key($this->resource->ownerType()),
            'tags'              => [], //Todo: add hashtag
            'attachments'       => ResourceGate::items($this->resource->attachments, false),
            'results'           => $this->getUserResults($context),
            'member_results'    => new ResultItemCollection($this->resource->results),
            'is_sponsored_feed' => (bool) $this->resource->sponsor_in_feed,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'extra'             => $this->getCustomExtra(),
            'feed_param'        => $this->getFeedParams(),
            'info'              => 'added_a_quiz',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getStatistic(): array
    {
        $reactItem = $this->resource->reactItem();

        return [
            'total_like'       => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_view'       => $this->resource->total_view,
            'total_share'      => $this->resource->total_share,
            'total_comment'    => $reactItem instanceof HasTotalComment ? $reactItem->total_comment : 0,
            'total_reply'      => $reactItem instanceof HasTotalCommentWithReply ? $reactItem->total_reply : 0,
            'total_attachment' => $this->resource->total_attachment,
            'total_play'       => $this->resource->total_play,
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
