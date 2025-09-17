<?php

namespace MetaFox\User\Listeners;

use MetaFox\Platform\Contracts\User as UserContracts;
use MetaFox\User\Models\User as UserModels;
use MetaFox\User\Support\Facades\UserPrivacy;

class GetPrivacyDetailOnOwnerListener
{
    public function handle(?UserContracts $context, UserContracts $owner): ?array
    {
        if (!$owner instanceof UserModels) {
            return null;
        }

        $privacy = UserPrivacy::getProfileSetting($owner->entityId(), 'feed:view_wall');

        $privacyDetail = app('events')->dispatch(
            'activity.get_privacy_detail',
            [$context, $owner, $privacy, false],
            true
        );

        $privacyDetail['label'] = $privacyDetail['tooltip'];

        $privacyDetail['tooltip'] = __p('core::phrase.tooltip_privacy_display_name_control_who_can_see_this', [
            'display_name' => $owner->full_name,
        ]);

        return $privacyDetail;
    }
}
