<?php

namespace MetaFox\User\Listeners;

use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\User\Support\Facades\UserBlocked;

class UnBlockedListener
{
    /**
     * @param  ContractUser  $user
     * @param  ContractUser  $owner
     * @return void
     */
    public function handle(ContractUser $user, ContractUser $owner): void
    {
        UserBlocked::unBlockUser($user, $owner);
    }
}
