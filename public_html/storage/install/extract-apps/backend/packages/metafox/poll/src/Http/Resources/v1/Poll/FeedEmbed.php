<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\ShareFeedInfoTrait;
use MetaFox\Poll\Http\Resources\v1\Traits\IsUserVoted;
use MetaFox\Poll\Http\Resources\v1\Traits\PollHasExtra;
use MetaFox\Poll\Models\Poll as Model;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;

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
 * Class PollEmbed.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FeedEmbed extends JsonResource
{
    use PollHasExtra;
    use IsUserVoted;
    use ShareFeedInfoTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $context = user();

        $description = '';

        $pollText = $this->resource->pollText;

        if (null !== $pollText) {
            $description = parse_output()->getDescription($pollText->text_parsed);
        }

        $postOnOther = $this->resource->userId() != $this->resource->ownerId();

        $ownerResource = null;

        if ($postOnOther && null !== $this->resource->ownerEntity) {
            $ownerResource = ResourceGate::user($this->resource->ownerEntity);
        }

        $taggedFriends = $this->getTaggedFriendItems($this->resource, 3);

        return [
            'id'                   => $this->resource->entityId(),
            'module_name'          => $this->resource->entityType(),
            'resource_name'        => $this->resource->entityType(),
            'question'             => ban_word()->clean($this->resource->question),
            'description'          => $description,
            'image'                => $this->resource->images,
            'privacy'              => $this->resource->privacy,
            'total_vote'           => $this->resource->total_vote,
            'is_featured'          => $this->resource->is_featured,
            'is_sponsor'           => $this->resource->is_sponsor,
            'is_user_voted'        => $this->isUserVoted($context),
            'answers'              => ResourceGate::items($this->resource->answers, false),
            'close_time'           => $this->resource->closed_at,
            'is_closed'            => $this->resource->is_closed,
            'is_multiple'          => (bool) $this->resource->is_multiple,
            'public_vote'          => (bool) $this->resource->public_vote,
            'link'                 => $this->resource->toLink(),
            'statistic'            => $this->getStatistic(),
            'extra'                => $this->getPollExtra(),
            'attachments'          => ResourceGate::items($this->resource->attachments, false),
            'user'                 => ResourceGate::user($this->resource->userEntity),
            'parent_user'          => $ownerResource,
            'info'                 => 'added_a_poll',
            'creation_date'        => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date'    => Carbon::parse($this->resource->updated_at)->toISOString(),
            'status'               => $this->resource->caption,
            'feed_status'          => $this->toFeedContent($this->resource),
            'location'             => $this->toLocation($this->resource),
            'tagged_friends'       => new UserEntityCollection($taggedFriends),
            'total_friends_tagged' => $this->resource->total_tag_friend,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        return [
            'total_like'       => $this->resource->total_like,
            'total_view'       => $this->resource->total_view,
            'total_comment'    => $this->resource->total_comment,
            'total_attachment' => $this->resource->total_attachment,
            'total_vote'       => $this->resource->total_vote,
            'total_share'      => $this->resource->total_share,
        ];
    }
}
