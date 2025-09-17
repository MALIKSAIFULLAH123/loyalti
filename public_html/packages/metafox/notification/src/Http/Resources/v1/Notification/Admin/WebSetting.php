<?php

namespace MetaFox\Notification\Http\Resources\v1\Notification\Admin;

use MetaFox\Platform\Resource\WebSetting as Main;

class WebSetting extends Main
{
    protected function initialize(): void
    {
        $this->add('markAllAsRead')
            ->apiUrl('notification/markAllAsRead')
            ->asPost();

        $this->add('deleteAll')
            ->apiUrl('notification/all')
            ->asDelete()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('notification::phrase.delete_confirm_all'),
                ]
            );

        $this->add('markAsRead')
            ->apiUrl('notification/:id')
            ->asPut();

        $this->add('markAsUnread')
            ->apiUrl('notification/unread/:id')
            ->asPut();

        $this->add('deleteItem')
            ->asDelete()
            ->apiUrl('notification/:id');
    }
}
