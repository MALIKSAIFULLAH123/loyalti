<?php
namespace MetaFox\Chat\Http\Resources\v1\Message;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

class WebSetting extends ResourceSetting
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
                    'truthy', 'room_id'
                ]
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

        $this->add('downloadItem')
            ->apiUrl('chat/download/:id');
    }
}
