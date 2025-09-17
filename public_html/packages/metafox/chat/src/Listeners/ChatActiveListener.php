<?php

namespace MetaFox\Chat\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;

class ChatActiveListener
{
    public function handle(?User $user): bool
    {
        return !empty(Settings::get('broadcast.connections.pusher.key'));
    }
}
