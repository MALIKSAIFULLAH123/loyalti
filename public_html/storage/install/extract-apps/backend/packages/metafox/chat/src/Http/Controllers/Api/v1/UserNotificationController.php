<?php

namespace MetaFox\Chat\Http\Controllers\Api\v1;

use MetaFox\Chat\Repositories\SubscriptionRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

class UserNotificationController extends ApiController
{
    public function __construct(public SubscriptionRepositoryInterface $subscriptionRepository)
    {
    }

    public function getNotification()
    {
        $context = user();

        $notification = $this->subscriptionRepository->getTotalUnseenNotification([
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ]);

        return $this->success($notification);
    }

    public function markAsSeen()
    {
        $context = user();

        $this->subscriptionRepository->markAsSeenNotification($context);

        $notification = $this->subscriptionRepository->getTotalUnseenNotification([
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ]);

        return $this->success($notification);
    }
}
