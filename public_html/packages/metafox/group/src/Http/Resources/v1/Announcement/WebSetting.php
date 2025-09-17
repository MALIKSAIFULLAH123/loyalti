<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Announcement;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->asGet()
            ->apiUrl('group-announcement')
            ->apiParams([
                'group_id' => ':group_id',
            ]);

        $this->add('markAsAnnouncement')
            ->apiUrl('group-announcement')
            ->asPost()
            ->apiParams([
                'group_id'  => ':group_id',
                'item_id'   => ':id',
                'item_type' => ':resource_name',
            ]);

        $this->add('markAsRead')
            ->apiUrl('group-announcement/hide')
            ->apiParams([
                'group_id' => ':group_id',
                'ann_id'   => ':id',
            ])
            ->asPost();

        $this->add('removeAnnouncement')
            ->apiUrl('group-announcement')
            ->asDelete()
            ->apiParams([
                'group_id'  => ':group_id',
                'item_id'   => ':id',
                'item_type' => ':resource_name',
            ]);
    }
}
