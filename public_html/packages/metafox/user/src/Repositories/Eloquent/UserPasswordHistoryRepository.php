<?php

namespace MetaFox\User\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\UserPasswordHistory;
use MetaFox\User\Repositories\UserPasswordHistoryRepositoryInterface;

class UserPasswordHistoryRepository extends AbstractRepository implements UserPasswordHistoryRepositoryInterface
{
    public function model(): string
    {
        return UserPasswordHistory::class;
    }

    public function getHistoryPasswords(int $userId, int $limit = 10)
    {
        return $this->getModel()->newQuery()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getLatestPassword(int $userId)
    {
        return $this->getModel()->newQuery()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();
    }
}
