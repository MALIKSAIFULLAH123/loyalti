<?php

namespace MetaFox\Authorization\Support;

use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\LoadReduce\Reducer;
use MetaFox\Platform\Contracts\User;

class LoadMissingUserRoles
{
    /**
     * @param  Reducer    $reducer
     * @return array|null
     */
    public function handle($reducer): ?array
    {
        $ids = $reducer->entities()
            ->filter(fn ($x) => $x instanceof User)
            ->map(fn ($x) => $x->id)
            ->unique();

        if ($ids->isEmpty()) {
            return null;
        }

        $key  = fn ($id) => sprintf('user::roles.first(user:%s)', $id);
        $key2 = fn ($id) => sprintf('user::roleIds(user:%s)', $id);

        return Role::query()
            ->join('auth_model_has_roles as pivot', 'auth_roles.id', '=', 'pivot.role_id')
            ->whereIn('pivot.model_id', $ids)
            ->get()
            ->reduce(function ($carry, $role) use ($key, $key2) {
                $carry[$key($role->model_id)] = $role;

                // keep list of roles.
                $carry[$key2($role->model_id)][] = $role->id;

                return $carry;
            }, []);
    }
}
