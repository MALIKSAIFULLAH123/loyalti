<?php

namespace MetaFox\Story\Http\Resources\v1\Story;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\Story\Models\Story as Model;
use MetaFox\Story\Support\Facades\StoryFacades;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class StoryDetail.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class StoryDetail extends JsonResource
{
    use HasExtra;
    use HasStatistic;
    use IsFriendTrait;
    use IsLikedTrait;
    use HasHashtagTextTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $storyText = $this->resource->storyText;
        $context   = user();
        $text      = '';

        if ($storyText) {
            $text = $this->getTransformContent($storyText->text_parsed);
            $text = parse_output()->parse($text);
        }
        $reactItem        = $this->resource->reactItem();
        $extraParams      = $this->getExtraParams();
        $existsBannedWord = Arr::get($extraParams, 'exists_banned_word', false);
        $thumbs           = $existsBannedWord
            ? $this->resource->default_images
            : $this->resource->thumbnails;

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'is_owner'          => $this->resource->isUser($context),
            'in_process'        => (int) $this->resource->is_processing,
            'is_liked'          => $this->isLike($context, $this->resource),
            'is_friend'         => $this->isFriend($context, $this->resource->user),
            'is_approved'       => $this->resource->isApproved(),
            'is_archive'        => $this->resource->is_archive,
            'is_muted'          => StoryFacades::isMuted($context, $this->resource->userId()),
            'view_id'           => $this->resource->view_id,
            'tags'              => [], //Todo: add hashtag
            'text'              => $text,
            'type'              => $this->resource->type,
            'comment_item_id'   => $reactItem->entityId(),
            'comment_type_id'   => $reactItem->entityType(),
            'content'           => $this->resource->toTitle(),
            'privacy'           => $this->resource->privacy,
            'duration'          => Arr::get($extraParams, 'duration') ?? $this->resource->duration,
            'has_seen'          => StoryFacades::hasSeen($context, $this->resource),
            'expired_at'        => $this->resource->expired_at,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'background'        => ResourceGate::embed($this->resource->storyBackground, false),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'image'             => $this->getImages(),
            'thumbs'            => $thumbs,
            'video'             => $this->resource->video,
            'reactions'         => StoryFacades::getReactionByUser($context, $this->resource),
            'statistic'         => $this->getStatistic(),
            'extra'             => $this->getExtra(),
            'extra_params'      => $extraParams,
            'url'               => $this->resource->toUrl(),
            'link'              => $this->resource->toLink(),
            'router'            => $this->resource->toRouter(),
            'related_comments'  => $this->relatedCommentsItemDetail($context, $this->resource),
            'relevant_comments' => $this->when($request->get('comment_id', false), $this->getRelevantComments()),
            'set_id'            => $this->resource->set_id,
            'item_id'           => $this->resource->item_id,
            'item_type'         => $this->resource->item_type,
            'expand_link'       => $this->resource->expand_link,
        ];
    }

    public function relatedCommentsItemDetail(User $context, ?Entity $content = null, int $limit = 6): JsonResource
    {
        if (!$content instanceof HasTotalComment) {
            return new JsonResource([]);
        }

        /** @var JsonResource|mixed $response */
        $response = app('events')->dispatch(
            'comment.related_comments.item_detail',
            [$context, $content, $limit],
            true
        );

        if (!$response instanceof JsonResource) {
            return new JsonResource([]);
        }

        return $response;
    }

    protected function getRelevantComments(): ?ResourceCollection
    {
        $commentId = request()->get('comment_id');

        if (!$commentId) {
            return null;
        }

        $reactItem = $this->resource->reactItem();

        return app('events')->dispatch('comment.relevant_comment_by_id', [user(), $commentId, $reactItem, false], true);
    }

    protected function getImages(): ?array
    {
        return $this->resource->images ?? $this->resource->thumbnails;
    }

    protected function getExtraParams(): array
    {
        $response = app('events')->dispatch('story.resource.get_extra_param', [user(), $this->resource], true);
        $extra    = $this->resource->extra ?? [];

        $extra = $this->parseTexts($extra);

        if (is_array($response)) {
            return array_merge($extra, $response);
        }

        return $extra;
    }

    protected function parseTexts(array $extra): array
    {
        $arrays = Arr::get($extra, 'texts');

        if (empty($arrays)) {
            return $extra;
        }

        $existsBannedWord = false;
        $arrays           = Arr::map($arrays, function (mixed $item) use (&$existsBannedWord) {
            if (!is_array($item)) {
                return $item;
            }

            if (!Arr::has($item, 'text')) {
                return $item;
            }

            $text      = Arr::get($item, 'text');
            $valueText = htmlspecialchars($text);
            $valueText = $this->getTransformContent($valueText);
            $valueText = parse_output()->parse($valueText);
            $textClean = ban_word()->clean($text);

            if (!$existsBannedWord) {
                $existsBannedWord = !Str::is($text, $textClean);
            }

            Arr::set($item, 'text_parsed', ban_word()->clean($valueText));
            Arr::set($item, 'text', $textClean);
            return $item;
        });

        Arr::set($extra, 'texts', $arrays);
        Arr::set($extra, 'exists_banned_word', $existsBannedWord);

        return $extra;
    }
}
