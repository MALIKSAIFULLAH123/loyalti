<?php

namespace MetaFox\Chat\Http\Resources\v1\Room;

use MetaFox\Platform\Resource\MobileSetting as Setting;

class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('chat-room');

        $this->add('viewItem')
            ->asGet()
            ->apiUrl('chat-room/:id');

        $this->add('addItem')
            ->asGet()
            ->apiUrl('core/mobile/form/chat.chat_room.create_room');

        $this->add('deleteItem')
            ->asDelete()
            ->apiUrl('chat-room/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('chat::phrase.delete_confirm'),
                ]
            );

        $this->add('getAddForm')
            ->asGet()
            ->apiUrl('core/mobile/form/chat.chat_room.create_room');
    }
}
