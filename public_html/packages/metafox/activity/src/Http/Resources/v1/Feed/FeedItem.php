<?php

namespace MetaFox\Activity\Http\Resources\v1\Feed;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Share;
use MetaFox\Activity\Policies\FeedPolicy;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Activity\Support\Facades\Snooze;
use MetaFox\Activity\Traits\FeedSupport;
use MetaFox\Core\Http\Resources\v1\Error\Forbidden;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\User as UserContract;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFox;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserEntity;

/**
 * Class FeedItem.
 * Do not use Gate in here to improve performance.
 *
 * @property Feed $resource
 */
class FeedItem extends JsonResource
{
    use FeedSupport;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        $profileId = $request->get('user_id', 0);

        $item         = $this->resource->item;
        $actionItem   = $this->getActionResource();
        $context      = user();
        $userEntity   = $this->resource->userEntity;
        $ownerEntity  = $this->resource->ownerEntity;
        $userResource = $ownerResource = null;

        if (null !== $ownerEntity) {
            $ownerResource = ResourceGate::user($ownerEntity);
        }

        if (null !== $userEntity) {
            $userResource = ResourceGate::user($userEntity);
        }

        $actionPhrase = $this->getTypeManager()->getTypePhraseWithContext($this->resource, $profileId);

        $taggedFriends = $this->getTaggedFriendsForFeed(3);

        $user = $this->resource->user;

        $owner = $this->resource->owner;

        $reactItem = $this->getReactItem($actionItem);

        $isOwnerTagged = false;

        if (!$this->isPostOnOther() && $owner instanceof User) {
            $isOwnerTagged = $this->isTagged($owner, $profileId, $taggedFriends);
        }

        $embedObject = [];

        if ($item instanceof Entity) {
            $embedObject = $this->getEmbedObject($item);
        }

        $status = $this->getParsedContent();

        if ($embedObject instanceof Forbidden && $item->hide_status_when_error_view_permission) {
            $status = null;
        }

        request()->request->set('comment_item_model', $this->resource);

        $comments = $this->getRelatedComments($context, $reactItem);

        if ($comments instanceof ResourceCollection) {
            $comments = $comments->toArray($request);
        }

        $data = [
            'id'                   => $this->resource->entityId(),
            'module_name'          => $this->resource->entityType(),
            'resource_name'        => $this->resource->entityType(),
            'type_id'              => $this->resource->type_id,
            'like_type_id'         => $reactItem->entityType(),
            'like_item_id'         => $reactItem->entityId(),
            'comment_type_id'      => $reactItem->entityType(),
            'comment_item_id'      => $reactItem->entityId(),
            'item_type'            => $this->resource->itemType(),
            'item_id'              => $this->resource->itemId(),
            'info'                 => $actionPhrase,
            'status'               => $status,
            'invisible'            => $user instanceof User ? $user->is_invisible : false,
            'tagged_friends'       => new UserEntityCollection($taggedFriends),
            'total_friends_tagged' => $this->getTotalFriendsTagged($actionItem),
            'location'             => $this->getLocation(),
            'is_sponsor'           => $this->isSponsored($request),
            'click_ref'            => null, // @todo ??
            'user'                 => $userResource,
            'statistic'            => $this->getStatistic(),
            'embed_object'         => $embedObject,
            'parent_user'          => $this->getParentUser(),
            'owner'                => $ownerResource,
            'role_label'           => $this->getRoleLabelInOwner($user, $owner),
            'privacy'              => $this->resource->privacy,
            'like_phrase'          => null, // @todo not used, consider to remove.
            'is_shared_feed'       => $this->resource->total_share > 0,
            'is_hidden'            => $this->getHideFeedService()->isHide($context, $this->resource),
            'is_hidden_all'        => Snooze::isSnoozeForever($context, $owner),
            'is_just_hide'         => false,
            'is_just_remove_tag'   => false,
            'is_show_location'     => $this->isShowLocation($reactItem),
            'user_full_name'       => $userEntity instanceof UserEntity ? $userEntity->name : null,
            'owner_id'             => $ownerEntity instanceof UserEntity ? $ownerEntity->entityId() : null,
            'owner_full_name'      => $ownerEntity instanceof UserEntity ? $ownerEntity->name : null,
            'creation_date'        => $this->resource->created_at,
            'modification_date'    => $this->resource->updated_at,
            'link'                 => $this->resource->toLink(),
            'url'                  => $this->resource->toUrl(),
            'extra'                => $this->getFeedExtra(),
            'is_saved'             => $context->can('isSavedItem', [Feed::class, $this->resource]),
            'status_background'    => $this->getBackgroundStatus(),
            'is_liked'             => $this->isLike($context, $reactItem),
            'is_pending'           => $this->resource->is_pending,
            'related_comments'     => $comments,
            'relevant_comments'    => null,
            'user_reacted'         => $this->userReacted($context, $reactItem),
            'most_reactions'       => $this->userMostReactions($context, $reactItem),
            'most_reactions_information' => $this->getItemReactionAggregation($context, $reactItem),
            'privacy_detail'       => ActivityFeed::getPrivacyDetail(
                $context,
                $this->resource,
                $this->resource->owner?->getRepresentativePrivacy()
            ),
            'pins'                    => app('activity.pin')->getPinOwnerIds($context, $this->resource->id),
            'is_owner_tagged'         => $isOwnerTagged,
            'from_resource'           => $this->resource->from_resource,
            'is_hide_tagged_headline' => $this->isHideTaggedHeadline(),
        ];

        request()->request->remove('comment_item_model');

        return array_merge($data, $this->getSharedInformation());
    }

    // Get sharedUser, sharedOwner full name
    protected function getSharedInformation(): array
    {
        $item = $this->resource->item;

        if (!$item instanceof Share) {
            return [];
        }

        $item->loadMissing(['item']);
        $content = $item->item;

        if (!$content instanceof Content) {
            return [];
        }

        $userEntity  = $content->userEntity;
        $ownerEntity = $content->ownerEntity;

        return [
            'shared_user_full_name'  => $userEntity instanceof UserEntity ? $userEntity->name : null,
            'shared_owner_full_name' => $ownerEntity instanceof UserEntity ? $ownerEntity->name : null,
        ];
    }

    protected function getEmbedObject(Entity $item): mixed
    {
        if (!$item instanceof HasPrivacy && !policy_check(FeedPolicy::class, 'view', user(), $this->resource)) {
            return new Forbidden($item);
        }

        $resource = ResourceGate::asJson($item, 'feed_embed');

        if (null !== $resource) {
            return $resource;
        }

        return ResourceGate::embed($item);
    }

    protected function isTagged(UserContract $owner, int $profileId, array $taggedFriends = []): bool
    {
        if ($profileId == 0) {
            return false;
        }

        if ($owner->entityId() == $profileId) {
            return false;
        }

        $collection = collect($taggedFriends);

        return $collection->contains('id', '=', $profileId);
    }

    protected function isSponsored(Request $request): bool
    {
        $currentSponsoredFeedIds = $request->get('current_sponsored_feed_ids');

        if (!is_array($currentSponsoredFeedIds)) {
            return false;
        }

        return in_array($this->resource->entityId(), $currentSponsoredFeedIds);
    }

    private function getRelatedComments(User $context, ?Entity $content = null)
    {
        /*
         * @deprecated v5.2
         */
        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.7', '>=')) {
            return null;
        }

        return $this->relatedComments($context, $content);
    }
}
