<?php

namespace MetaFox\Chat\Http\Resources\v1\Room;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('chat-room');

        $this->add('viewItem')
            ->asGet()
            ->apiUrl('chat-room/:id');

        $this->add('addItem')
            ->asPost()
            ->apiUrl('chat-room');

        $this->add('deleteItem')
            ->asDelete()
            ->apiUrl('chat-room/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('chat::phrase.delete_confirm'),
                ]
            );

        $this->add('addForm')
            ->asGet()
            ->apiUrl('chat-room/addForm');

        $this->add('getNotification')
            ->asGet()
            ->apiUrl('chat-user-notification/notification');

        $this->add('markAsSeenNotification')
            ->asPut()
            ->apiUrl('chat-user-notification/mark-seen');
    }
}
