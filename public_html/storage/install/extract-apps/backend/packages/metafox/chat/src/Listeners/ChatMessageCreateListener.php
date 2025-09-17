<?php

namespace MetaFox\Chat\Listeners;

use MetaFox\Chat\Repositories\MessageRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class ChatMessageCreateListener
{
    public function handle(User $user, array $attribute)
    {
        $message = resolve(MessageRepositoryInterface::class)->addMessage($user, $attribute);

        return $message;
    }
}
