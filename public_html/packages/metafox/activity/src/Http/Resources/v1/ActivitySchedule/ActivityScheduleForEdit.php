<?php

namespace MetaFox\Activity\Http\Resources\v1\ActivitySchedule;

use Illuminate\Http\Request;
use MetaFox\Activity\Models\ActivitySchedule as Model;
use MetaFox\Activity\Repositories\ActivityScheduleRepositoryInterface;
use MetaFox\Form\PrivacyOptionsTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class ActivityScheduleItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ActivityScheduleForEdit extends ActivityScheduleItem
{
    use PrivacyOptionsTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $resource     = $this->resource;
        $parentUserId = 0;
        $userEntity   = $this->resource->userEntity;
        $ownerEntity  = $this->resource->ownerEntity;

        if ($userEntity instanceof User && $ownerEntity instanceof User) {
            if ($userEntity->entityId() != $ownerEntity->entityId()) {
                $parentUserId = $ownerEntity->entityId();
            }
        }

        $itemId   = 0;
        $itemType = null;

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
        $taggedFriends = $this->getScheduleRepository()->getScheduledTaggedFriend($resource, 200);

        $data = app('events')->dispatch('activity.schedule.listing.render_normalization', [$resource->data], true);

        if (null === $data) {
            $data = $resource->data ?? [];
        }

        return [
            'id'            => $resource->id,
            'module_name'   => 'feed',
            'resource_name' => $resource->entityType(),
            'schedule_time' => $resource->schedule_time,
            'post_type'     => $resource->post_type,
            'item'          => array_merge([
                'post_type'       => $resource->post_type,
                'tagged_friends'  => new UserEntityCollection($taggedFriends),
                'embed_object'    => $this->getScheduleRepository()->getScheduledEmbedObject($resource, true),
                'status_text'     => $resource->content,
                'parent_user_id'  => $parentUserId,
                'privacy_options' => $this->getPrivacyOptions(),
            ], $this->getAdditionData($data,  true)),
            'item_type' => $itemType,
            'item_id'   => $itemId,
        ];
    }

    public function getScheduleRepository(): ActivityScheduleRepositoryInterface
    {
        return resolve(ActivityScheduleRepositoryInterface::class);
    }
}
