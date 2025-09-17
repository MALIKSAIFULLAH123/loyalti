<?php

namespace MetaFox\User\Repositories;

interface UserPasswordHistoryRepositoryInterface
{
    public function getHistoryPasswords(int $userId, int $limit = 10);

    public function getLatestPassword(int $userId);
}
