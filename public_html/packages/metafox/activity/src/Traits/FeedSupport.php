<?php

namespace MetaFox\Activity\Traits;

use Illuminate\Support\Arr;
use MetaFox\Activity\Contracts\ActivityHiddenManager;
use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Type;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasBackGroundStatus;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\Contracts\User as UserContract;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Helpers\UserReactedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;

/**
 * @property Feed $resource
 */
trait FeedSupport
{
    use HasStatistic;
    use IsFriendTrait;
    use FeedExtra;
    use IsLikedTrait;
    use UserReactedTrait;
    use RelatedCommentsTrait;
    use HasTagTrait;

    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        $resourceForShare = $this->getActionResourceOnShare();

        $item = $this->getActionResource();

        $react = $item;

        // In case some entities has feed but not really a content
        if ($item instanceof Content) {
            $react = $item->reactItem();
        }

        if (null === $resourceForShare) {
            $resourceForShare = $react;
        }

        return [
            'total_like'    => $react instanceof HasTotalLike ? max($react->total_like, 0) : 0,
            'total_comment' => $react instanceof HasTotalComment ? max($react->total_comment, 0) : 0,
            'total_reply'   => $react instanceof HasTotalCommentWithReply ? max($react->total_reply, 0) : 0,
            'total_view'    => $react instanceof HasTotalView ? max($react->total_view, 0) : 0,
            'total_share'   => $resourceForShare instanceof HasTotalShare ? max($resourceForShare->total_share, 0) : 0,
        ];
    }

    protected function getTypeManager(): TypeManager
    {
        return resolve(TypeManager::class);
    }

    protected function getHideFeedService(): ActivityHiddenManager
    {
        return resolve(ActivityHiddenManager::class);
    }

    protected function getActionResource(): Entity
    {
        $result = $this->getTypeManager()->hasFeature($this->resource->type_id, Type::ACTION_ON_FEED_TYPE)
            ? $this->resource
            : $this->resource->item;

        if (!$result) {
            $result = $this->resource;
        }

        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getLocation(): ?array
    {
        $item = $this->getActionResource();

        $location = null;

        if ($item instanceof HasLocationCheckin) {
            [$address, $lat, $lng, , $fullAddress] = $item->toLocation();

            if ($address && $lat && $lng) {
                $location = [
                    'address'      => $address,
                    'lat'          => (float) $lat,
                    'lng'          => (float) $lng,
                    'full_address' => $fullAddress,
                ];

                if (is_bool($item->show_map_on_feed)) {
                    Arr::set($location, 'show_map', $item->show_map_on_feed);
                }
            }
        }

        return $location;
    }

    /**
     * @return mixed
     */
    protected function getBackgroundStatus()
    {
        $item = $this->getActionResource();

        if (!$item instanceof HasBackGroundStatus) {
            return null;
        }

        $backgroundStatus = $item->getBackgroundStatus();

        /*
         * @deprecated Remove in 5.1.13
         */
        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.12', '<')) {
            return $backgroundStatus?->images;
        }

        return $backgroundStatus;
    }

    /**
     * @param int $limit
     *
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function getTaggedFriendsForFeed(mixed $limit = 10): array
    {
        $item = $this->getActionResource();

        if (!$item instanceof HasTaggedFriend) {
            return [];
        }

        return $this->getTaggedFriendItems($item, $limit);
    }

    protected function getRoleLabelInOwner(?UserContract $user, ?UserContract $owner): ?string
    {
        if (!$user instanceof UserContract) {
            return null;
        }

        if ($owner instanceof HasPrivacyMember) {
            return $owner->getRoleLabel($user);
        }

        return null;
    }

    protected function isShowLocation(?Entity $reactItem): bool
    {
        if (!$reactItem instanceof Entity) {
            return true;
        }

        if (!method_exists($reactItem, 'isShowLocation')) {
            return true;
        }

        return $reactItem->isShowLocation();
    }

    protected function isHideTaggedHeadline(): bool
    {
        return $this->getTypeManager()->hasFeature($this->resource->type_id, Type::PREVENT_DISPLAY_TAG_ON_HEADLINE);
    }

    protected function isPostOnOther(): bool
    {
        return $this->resource->userId() != $this->resource->ownerId();
    }

    protected function getParentUser(): ?array
    {
        if (!$this->isPostOnOther()) {
            return null;
        }

        if (!$this->resource->ownerEntity) {
            return null;
        }

        return ResourceGate::user($this->resource->ownerEntity);
    }

    protected function getTotalFriendsTagged(Entity $actionItem): int
    {
        if (!$actionItem instanceof HasTaggedFriend) {
            return 0;
        }

        return $actionItem->total_tag_friend;
    }

    protected function getReactItem(Entity $actionItem): ?Entity
    {
        if ($actionItem instanceof Content) {
            return $actionItem->reactItem();
        }

        return $actionItem;
    }

    protected function getActionResourceOnShare(): ?Entity
    {
        if (!$this->resource->item instanceof Entity) {
            return null;
        }

        /*
         * Handle
         */
        if (true !== $this->resource->item->action_share_on_feed) {
            return null;
        }

        return $this->resource->item;
    }
}
