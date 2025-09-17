<?php

namespace MetaFox\GettingStarted\Repositories;

use MetaFox\Platform\Contracts\User;

interface UserFirstLoginRepositoryInterface
{
    public function initUserFirstLoginData(User $user): void;

    public function deleteByUser(User $user): void;

    public function isFirstLogin(User $context, string $resolution): bool;

    public function markFirstLogin(User $context, string $resolution): void;
}
