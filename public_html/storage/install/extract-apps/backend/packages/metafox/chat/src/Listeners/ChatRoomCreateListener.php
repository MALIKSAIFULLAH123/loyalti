<?php

namespace MetaFox\Chat\Listeners;

use MetaFox\Chat\Repositories\RoomRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class ChatRoomCreateListener
{
    public function handle(User $user, array $memberIds)
    {
        $room = resolve(RoomRepositoryInterface::class)->createChatRoom($user, $memberIds);

        return $room;
    }
}
