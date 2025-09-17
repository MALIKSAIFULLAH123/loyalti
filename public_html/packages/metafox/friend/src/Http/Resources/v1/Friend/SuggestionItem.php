<?php

namespace MetaFox\Friend\Http\Resources\v1\Friend;

use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityItem;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class SuggestionSimple.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SuggestionItem extends UserEntityItem
{
    public function toArray($request): array
    {
        $data = parent::toArray($request);

        return array_merge($data, [
            'privacy_feed' => $this->getPrivacyFeed(),
        ]);
    }

    protected function getPrivacyFeed(): ?array
    {
        $privacy = UserPrivacy::getProfileSetting($this->resource->entityId(), 'feed:view_wall');

        $privacyDetail = app('events')->dispatch(
            'activity.get_privacy_detail',
            [user(), $this->resource->detail, $privacy, false],
            true
        );

        if (!is_array($privacyDetail)) {
            return null;
        }

        $privacyDetail['label'] = $privacyDetail['tooltip'];

        if (!$this->resource instanceof HasPrivacyMember) {
            $privacyDetail['tooltip'] = __p('core::phrase.tooltip_privacy_display_name_control_who_can_see_this', [
                'display_name' => $this->resource->name,
            ]);
        }

        return $privacyDetail;
    }
}
