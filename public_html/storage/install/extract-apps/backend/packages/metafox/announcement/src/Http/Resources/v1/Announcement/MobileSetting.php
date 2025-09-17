<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Announcement\Http\Resources\v1\Announcement;

use MetaFox\Platform\Resource\MobileSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * Announcement Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewItem')
            ->apiUrl('announcement/:id')
            ->pageUrl('announcement/:id');

        $this->add('viewAll')
            ->apiUrl('announcement')
            ->asGet();

        $this->add('markAsRead')
            ->apiUrl('announcement/view')
            ->asPost();

        $this->add('viewAnalytic')
            ->apiUrl('announcement/view')
            ->apiParams([
                'announcement_id' => ':id',
            ])
            ->asGet();

        $this->add('closeAnnouncement')
            ->apiUrl('announcement/close')
            ->asPost();
    }
}
