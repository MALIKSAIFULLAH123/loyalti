<?php

namespace MetaFox\Chat\Traits;

use MetaFox\Chat\Http\Resources\v1\Message\LastMessageDetail;
use MetaFox\Chat\Models\Room;
use MetaFox\Chat\Models\Subscription;
use MetaFox\Chat\Repositories\MessageRepositoryInterface;
use MetaFox\Chat\Repositories\SubscriptionRepositoryInterface;
use MetaFox\Platform\ResourcePermission as ACL;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;

trait RoomInfoTraits
{
    public function getOtherMembers(Room $resource): array
    {
        $members       = [];
        $roomId        = $resource->id;
        $context       = user();
        $subscriptions = resolve(SubscriptionRepositoryInterface::class)->getSubscriptions($roomId, true, $context->entityId());

        foreach ($subscriptions as $subscription) {
            $user      = new UserEntityDetail($subscription->userEntity);
            $members[] = $user;
        }

        return $members;
    }

    public function getLastMessage(int $userId, Room $resource)
    {
        $message = resolve(MessageRepositoryInterface::class)->getRoomLastMessage($userId, $resource->id);

        return new LastMessageDetail($message);
    }

    public function getChatRoomName(Room $resource): string
    {
        $roomId        = $resource->id;
        $context       = user();
        $subscriptions = resolve(SubscriptionRepositoryInterface::class)->getSubscriptions($roomId, true, $context->entityId());

        if ($subscriptions->count() < 1) {
            return '';
        } else {
            /** @var Subscription $subscription */
            $subscription = $subscriptions->first();
            $user         = $subscription->userEntity;

            return (!$user || $user->isDeleted()) ? __p('core::phrase.deleted_user') : $user->name;
        }
    }

    public function getTotalUnseen(Room $resource): int
    {
        $roomId        = $resource->id;
        $context       = user();
        $subscriptions = resolve(SubscriptionRepositoryInterface::class)->getSubscriptions($roomId, false, $context->entityId());

        if ($subscriptions->count()) {
            $subscription = $subscriptions->first();

            return $subscription->total_unseen;
        }

        return 0;
    }

    public function getRelatedSubscriptionInfo(Room $resource): array
    {
        $context = user();
        $data    = [
            'is_block'  => 0,
            'is_showed' => 0,
        ];
        $hasDeletedUser = false;
        if ($resource->subscriptions()->count()) {
            $resource->subscriptions()->each(function (Subscription $subscription) use (&$data, &$hasDeletedUser, $context) {
                if ($subscription->userId() == $context->entityId()) {
                    $data['is_block']  = $subscription->is_block;
                    $data['is_showed'] = $subscription->is_showed;
                }
                if (!$subscription->userEntity || $subscription->userEntity->isDeleted()) {
                    $hasDeletedUser = true;
                }
            });
        }

        if ($hasDeletedUser) {
            $data['is_block'] = true; // Block room chat is one of member is deleted
        }

        return $data;
    }

    public function getExtra(): array
    {
        $context = user();

        $resource = $this->resource;

        return [
            ACL::CAN_DELETE => $context->can('delete', [$resource, $resource]),
        ];
    }
}
