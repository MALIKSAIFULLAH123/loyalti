<?php

namespace MetaFox\Activity\Http\Resources\v1\ActivitySchedule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Activity\Models\ActivitySchedule as Model;
use MetaFox\Activity\Repositories\ActivityScheduleRepositoryInterface;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Activity\Traits\FeedSupport;
use MetaFox\Activity\Traits\ScheduledFeedExtra;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;

/**
 * Class ActivityScheduleItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ActivityScheduleItem extends JsonResource
{
    use FeedSupport;
    use ScheduledFeedExtra;

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $resource     = $this->resource;
        $status       = $this->getParsedContent();
        $userEntity   = $this->resource->userEntity;
        $ownerEntity  = $this->resource->ownerEntity;
        $postOnOther  = $this->resource->userId() != $this->resource->ownerId();
        $userResource = $ownerResource = null;

        $data = app('events')->dispatch('activity.schedule.listing.render_normalization', [$resource->data], true);

        if (null === $data) {
            $data = $resource->data ?? [];
        }

        if ($postOnOther && null !== $ownerEntity) {
            $ownerResource = ResourceGate::user($ownerEntity);
        }

        if (null !== $userEntity) {
            $userResource = ResourceGate::user($userEntity);
        }
        $taggedFriendIds = Arr::get($data, 'tagged_friends', []);
        $taggedFriends   = $this->getScheduleRepository()->getScheduledTaggedFriend($resource);

        return array_merge([
            'id'                   => $resource->id,
            'item_id'              => $resource->id,
            'item_type'            => $resource->entityType(),
            'module_name'          => 'feed',
            'info'                 => 'user_schedule_a_post_on_timeline',
            'resource_name'        => $resource->entityType(),
            'schedule_time'        => $resource->schedule_time,
            'post_type'            => $resource->post_type,
            'tagged_friends'       => new UserEntityCollection($taggedFriends),
            'total_friends_tagged' => count($taggedFriendIds),
            'embed_object'         => $this->getScheduleRepository()->getScheduledEmbedObject($resource),
            'status'               => $status,
            'user'                 => $userResource,
            'parent_user'          => $ownerResource,
            'owner'                => ResourceGate::user($this->resource->ownerEntity),
            'creation_date'        => $resource->created_at,
            'extra'                => $this->getScheduleFeedExtra(),
        ], $this->getAdditionData($data));
    }

    public function getScheduleRepository(): ActivityScheduleRepositoryInterface
    {
        return resolve(ActivityScheduleRepositoryInterface::class);
    }

    public function getAdditionData(array $data, bool $isEdit = false): array
    {
        $context            = user();

        $statusBackgroundId = Arr::get($data, 'status_background_id', 0);

        $location           = null;

        if (Arr::get($data, 'location_name')) {
            $location = [
                'address' => Arr::get($data, 'location_name'),
                'lat'     => Arr::get($data, 'location_latitude'),
                'lng'     => Arr::get($data, 'location_longitude'),
                'full_address' => Arr::get($data, 'location_address'),
            ];

            if (Arr::has($data, 'show_map_on_feed')) {
                Arr::set($location, 'show_map', Arr::get($data, 'show_map_on_feed'));
            }
        }

        $privacyDetail = ActivityFeed::getPrivacyDetail(
            $context,
            $this->resource,
            $this->resource->owner?->getRepresentativePrivacy()
        );

        if ($isEdit) {
            $privacyDetail = app('events')->dispatch(
                'activity.get_privacy_detail_on_owner',
                [$context, $this->resource->owner],
                true
            );
        }

        return [
            'location'             => $location,
            'privacy'              => Arr::get($data, 'privacy'),
            'privacy_detail'       => $privacyDetail,
            'status_background_id' => $statusBackgroundId,
            'status_background'    => ActivityFeed::getBackgroundStatus($statusBackgroundId),
        ];
    }
}
