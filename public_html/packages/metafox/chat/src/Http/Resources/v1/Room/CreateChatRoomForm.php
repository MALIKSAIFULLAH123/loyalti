<?php

namespace MetaFox\Chat\Http\Resources\v1\Room;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

class CreateChatRoomForm extends AbstractForm
{
    protected function prepare(): void
    {
        $user = user();

        $friends =  app('events')->dispatch('friend.simple_friends', [
            $user, $user, ['q' => ''],
        ], true);

        $this->asPost()->title(__p('chat::phrase.new_conversation'))
            ->submitAction('@chat/addConversation')
            ->action(url_utility()->makeApiUrl('chat-room'))
            ->setValue([
                'users' => $friends,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::friendPicker('users')
                    ->multiple(false)
                    ->placeholder(__p('friend::phrase.search_for_a_friend'))
            );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->disableWhenClean()
                    ->label(__p('core::phrase.create')),
                Builder::cancelButton(),
            );
    }
}
