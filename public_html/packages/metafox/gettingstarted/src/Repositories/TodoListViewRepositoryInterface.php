<?php

namespace MetaFox\GettingStarted\Repositories;

use MetaFox\Platform\Contracts\User;

interface TodoListViewRepositoryInterface
{
    public function markDone(User $user, array $attributes): void;

    public function isDone(int $todoListId, int $userId): bool;

    public function checkViewExist(array $conditions): bool;
}
