<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Notification\Http\Resources\v1\Notification;

use MetaFox\Notification\Support\Browse\Scopes\ViewScope;
use MetaFox\Platform\Resource\MobileSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * Friend Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('notification')->apiRules([
                'view' => [
                    'includes', 'view', ViewScope::getAllowView(),
                ],
            ]);

        $this->add('deleteItem')
            ->apiUrl('notification/:id')
            ->asDelete()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('notification::phrase.delete_confirm'),
                ]
            );
        $this->add('deleteAll')
            ->apiUrl('notification/all')
            ->asDelete()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('notification::phrase.delete_confirm_all'),
                ]
            );

        $this->add('markAllAsRead')
            ->apiUrl('notification/markAllAsRead')
            ->asPost();

        $this->add('markAsRead')
            ->apiUrl('notification/:id')
            ->asPut();

        $this->add('markAsUnread')
            ->apiUrl('notification/unread/:id')
            ->asPut();
    }
}
