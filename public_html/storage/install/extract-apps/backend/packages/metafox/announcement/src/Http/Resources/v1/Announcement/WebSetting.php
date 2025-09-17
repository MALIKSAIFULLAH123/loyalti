<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Announcement\Http\Resources\v1\Announcement;

use MetaFox\Platform\Resource\WebSetting as Setting;

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
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('announcement')
            ->apiRules([
                'last_id' => ':last_id',
            ]);

        $this->add('viewItem')
            ->apiUrl('announcement/:id')
            ->pageUrl('announcement/:id');

        $this->add('editItem')
            ->apiUrl('announcement/form/:id');

        $this->add('deleteItem')
            ->apiUrl('announcement/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('announcement::phrase.delete_confirm'),
                ]
            );

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
