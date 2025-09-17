<?php

namespace MetaFox\GettingStarted\Policies;

use MetaFox\GettingStarted\Support\Traits\TodoListTrait;
use MetaFox\Platform\Contracts\User;

class TodoListViewPolicy
{
    use TodoListTrait;

    protected string $type = 'todo_list_view';

    public function markDone(User $user): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        return true;
    }
}
