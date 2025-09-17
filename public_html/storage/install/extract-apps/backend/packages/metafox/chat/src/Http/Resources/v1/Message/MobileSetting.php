<?php

namespace MetaFox\Chat\Http\Resources\v1\Message;

use MetaFox\Platform\Resource\MobileSetting as Setting;

class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->asGet()
            ->apiUrl('chat')
            ->apiRules([
                'q' => [
                    'truthy', 'q',
                ],
                'room_id' => [
                    'truthy', 'room_id',
                ],
            ]);

        $this->add('addItem')
            ->asPost()
            ->apiRules([
                'type' => ['includes', 'type', ['text', 'delete', 'file']],
            ])
            ->apiUrl('chat');

        $this->add('removeItem')
            ->asPut()
            ->apiUrl('chat/remove/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('chat::phrase.delete_message_confirm'),
                ]
            );

        $this->add('editItem')
            ->asPut()
            ->apiUrl('chat/:id');

        $this->add('reactItem')
            ->asPut()
            ->apiUrl('chat/react/:id');
    }
}
