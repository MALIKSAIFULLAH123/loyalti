<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Mute;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('muteInGroupForm')
            ->apiUrl('core/form/group.group_mute.mute_in_group/:id')
            ->asGet()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id']);

        $this->add('unmuteInGroup')
            ->apiUrl('group-mute')
            ->asDelete()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id']);

        $this->add('viewMutedUsersInGroup')
            ->apiUrl('group-mute')
            ->asPatch()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id']);
    }
}
