<?php

namespace MetaFox\Activity\Http\Resources\v1\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Traits\FeedSupport;
use MetaFox\Form\PrivacyOptionsTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasBackGroundStatus;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;

/**
 * Class FeedItem.
 * @property Feed $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FeedForEdit extends JsonResource
{
    use PrivacyOptionsTrait;
    use FeedSupport;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD)
     */
    public function toArray($request): array
    {
        $context = user();
        $request->request->add([
            'embed_object_for_edit' => true,
        ]);

        /** @var Content $item */
        $item = $this->resource->item;

        $location = null;

        if ($item instanceof HasLocationCheckin) {
            [$address, $lat, $lng, , $fullAddress] = $item->toLocation();

            if ($address && $lat && $lng) {
                $location = [
                    'address' => $address,
                    'lat'     => (float) $lat,
                    'lng'     => (float) $lng,
                    'full_address' => $fullAddress,
                ];

                if (is_bool($item->show_map_on_feed)) {
                    Arr::set($location, 'show_map', (int) $item->show_map_on_feed);
                }
            }
        }

        $parentUserId = 0;
        $userEntity   = $this->resource->userEntity;
        $ownerEntity  = $this->resource->ownerEntity;

        if ($userEntity instanceof User && $ownerEntity instanceof User) {
            if ($userEntity->entityId() != $ownerEntity->entityId()) {
                $parentUserId = $ownerEntity->entityId();
            }
        }

        $statusBackgroundId = 0;
        $itemId             = 0;
        $itemType           = null;

        if ($ownerEntity instanceof User) {
            if ($ownerEntity->entityType() == 'page') {
                $itemId   = $ownerEntity->entityId();
                $itemType = 'pages';
            }

            if ($ownerEntity->entityType() == 'group') {
                $itemId   = $ownerEntity->entityId();
                $itemType = 'groups';
            }
        }

        $privacy = $this->resource->privacy;

        if ($this->resource->privacy == MetaFoxPrivacy::CUSTOM) {
            $lists = PrivacyPolicy::getPrivacyItem($item);

            $listIds = [];
            if (!empty($lists)) {
                $listIds = array_column($lists, 'item_id');
            }

            $privacy = $listIds;
        }

        if ($item instanceof HasBackGroundStatus) {
            $statusBackgroundId = $item->status_background_id ?: 0;
        }

        $data = [
            'feed_id'   => $this->resource->entityId(),
            'item_type' => $itemType,
            'item_id'   => $itemId,
            'post_type' => $this->resource->itemType(),
            'extra'     => $this->getFeedExtra(),
            'item'      => [
                'status_text'          => $this->resource->content,
                'tagged_friends'       => new UserEntityCollection($this->getTaggedFriendsForFeed(200)),
                'location'             => $location,
                'parent_user_id'       => $parentUserId,
                'privacy'              => $privacy,
                'privacy_options'      => $this->getPrivacyOptions(),
                'status_background_id' => $statusBackgroundId,
                'status_background'    => $this->getBackgroundStatus(),
                'embed_object'         => ResourceGate::asEmbed($item),
            ],
        ];
        $privacyDetail = app('events')->dispatch(
            'activity.get_privacy_detail_on_owner',
            [$context, $this->resource->owner],
            true
        );
        if ($privacyDetail != null) {
            $data['item']['privacy_detail'] = $privacyDetail;
        }

        return $data;
    }
}
