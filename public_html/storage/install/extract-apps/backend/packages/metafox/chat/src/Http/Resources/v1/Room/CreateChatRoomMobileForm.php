<?php

namespace MetaFox\Chat\Http\Resources\v1\Room;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;

class CreateChatRoomMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->asPost()->title(__p('chat::phrase.new_conversation'))
            ->action(url_utility()->makeApiUrl('chat-room'))
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::friendPicker('users')
                    ->label(__p('chat::web.select_users'))
                    ->setComponent('InviteFriendPicker')
                    ->multiple(false)
                    ->apiEndpoint(url_utility()->makeApiUrl('friend'))
                    ->placeholder(__p('friend::phrase.search_for_a_friend'))
            );
    }
}
