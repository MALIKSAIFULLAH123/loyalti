<?php

namespace MetaFox\Forum\Support\Browse\Traits\ForumThread;

use Illuminate\Support\Arr;
use MetaFox\Forum\Support\Facades\ForumThread as ForumThreadFacade;
use MetaFox\Platform\ResourcePermission;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Contracts\Content;

trait ExtraTrait
{
    use HasExtra;

    public function getThreadExtra(): array
    {
        $resource = $this->resource;

        $extra = $this->getExtra();

        unset($extra[ResourcePermission::CAN_COMMENT]);
        unset($extra[ResourcePermission::CAN_PUBLISH]);

        $context = user();

        $customExtra = ForumThreadFacade::getCustomPolicies($context, $resource);

        $approvedStatus = !$resource->is_wiki && $resource->is_approved;

        $permissions = array_merge($extra, $customExtra, [
            ResourcePermission::CAN_SHARE => $this->canShare(),
        ]);

        $sponsorPermissions = [
            ResourcePermission::CAN_SPONSOR,
            ResourcePermission::CAN_PURCHASE_SPONSOR,
            ResourcePermission::CAN_UNSPONSOR,
            ResourcePermission::CAN_SPONSOR_IN_FEED,
            ResourcePermission::CAN_PURCHASE_SPONSOR_IN_FEED,
            ResourcePermission::CAN_UNSPONSOR_IN_FEED
        ];

        foreach ($sponsorPermissions as $sponsorPermission) {
            $permissions[$sponsorPermission] = Arr::get($permissions, $sponsorPermission, false) && $approvedStatus;
        }

        return $permissions;
    }

    protected function canShare(): bool
    {
        if (!$this->resource->isApproved()) {
            return false;
        }

        $context = user();

        if (!$context->hasPermissionTo('forum_thread.share')) {
            return false;
        }

        return true;
    }
}
