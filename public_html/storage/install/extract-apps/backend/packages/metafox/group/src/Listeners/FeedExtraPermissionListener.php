<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;

class FeedExtraPermissionListener
{
    public function handle(?User $context, ?Content $resource): array
    {
        if (!$resource instanceof Content) {
            return [];
        }

        if (!$resource->isApproved()) {
            return [];
        }

        $group    = $resource->owner;
        $itemType = $resource->entityType();
        $itemId   = $resource->entityId();
        /** @var GroupPolicy $groupPolicy */
        $groupPolicy = PolicyGate::getPolicyFor(Group::class);

        if (!$group instanceof Group) {
            return [];
        }

        if ($resource->itemType() !== 'activity_post') {
            return [];
        }

        $announceRepository = resolve(AnnouncementRepositoryInterface::class);

        $isExists = $announceRepository->checkExistsAnnouncement($group->entityId(), $itemId, $itemType);

        return [
            'can_mark_announcement'   => !$isExists && $groupPolicy->markAnnouncement($context, $group),
            'can_remove_announcement' => $isExists && $groupPolicy->markAnnouncement($context, $group),
        ];
    }
}
