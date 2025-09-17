<?php

namespace MetaFox\Photo\Support\Browse\Traits\PhotoGroup;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\ResourcePermission as ACL;

trait ExtraTrait
{
    use CheckModeratorSettingTrait;

    public function getExtra(): array
    {
        $user = user();

        $resource = $this->resource;

        $owner = $resource->owner;

        $hasPrivacy = $owner instanceof HasPrivacyMember;

        // In case group/event admins can approve pending items
        $canApprove = match ($hasPrivacy) {
            true  => $this->checkModeratorSetting($user, $owner, 'approve_or_deny_post'),
            false => $user->can('approve', [$resource, $resource]),
        };

        return [
            ACL::CAN_SEARCH           => true,
            ACL::CAN_VIEW             => $user->can('view', [$resource, $resource]),
            ACL::CAN_LIKE             => $user->can('like', [$resource, $resource]),
            ACL::CAN_SHARE            => $user->can('share', [$resource, $resource]),
            ACL::CAN_DELETE           => $user->can('delete', [$resource, $resource]),
            ACL::CAN_DELETE_OWN       => $user->can('deleteOwn', [$resource, $resource]),
            ACL::CAN_REPORT           => $user->can('reportItem', [$resource, $resource]),
            ACL::CAN_REPORT_TO_OWNER  => $user->can('reportToOwner', [$resource, $resource]),
            ACL::CAN_ADD              => $user->can('create', [$resource]),
            ACL::CAN_EDIT             => $user->can('update', [$resource, $resource]),
            ACL::CAN_COMMENT          => $user->can('comment', [$resource, $resource]),
            ACL::CAN_APPROVE          => $canApprove,
            ACL::CAN_SAVE_ITEM        => $user->can('saveItem', [$resource, $resource]),
        ];
    }
}
