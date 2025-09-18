<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Mute;

use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('muteInGroupForm')
            ->apiUrl('core/mobile/form/group.group_mute.mute_in_group/:id')
            ->asGet()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id']);

        $this->add('unmuteInGroup')
            ->apiUrl('group-mute')
            ->asDelete()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id']);

        $this->add('viewMutedUsersInGroup')
            ->apiUrl('group-mute')
            ->asGet()
            ->apiParams(['group_id' => ':id']);
    }
}
