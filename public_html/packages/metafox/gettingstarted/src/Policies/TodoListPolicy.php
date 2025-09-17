<?php

namespace MetaFox\GettingStarted\Policies;

use MetaFox\Platform\Contracts\User;

class TodoListPolicy
{
    protected string $type = 'todo_list';

    protected function hasAdminCPAccess(User $user): bool
    {
        if ($user->hasPermissionTo('admincp.has_admin_access')) {
            return true;
        }

        return false;
    }

    public function createAdminCP(User $user): bool
    {
        return $this->hasAdminCPAccess($user);
    }

    public function updateAdminCP(User $user): bool
    {
        return $this->hasAdminCPAccess($user);
    }

    public function deleteAdminCP(User $user): bool
    {
        return $this->hasAdminCPAccess($user);
    }

    public function viewAdminCP(User $user): bool
    {
        return $this->hasAdminCPAccess($user);
    }
}
