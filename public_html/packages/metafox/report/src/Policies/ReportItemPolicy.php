<?php

namespace MetaFox\Report\Policies;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\Report\Models\ReportItem as Resource;

class ReportItemPolicy
{
    use HasPolicyTrait;

    protected string $type = Resource::ENTITY_TYPE;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User       $user
     * @param  array|null $attributes
     * @return bool
     */
    public function create(User $user, ?array $attributes = []): bool
    {
        if (!Arr::has($attributes, 'item_type')) {
            return false;
        }

        $itemType         = Arr::get($attributes, 'item_type', '');
        $entityPermission = "$itemType.report";

        if (!$user->hasPermissionTo($entityPermission)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user): bool
    {
        return true;
    }
}
