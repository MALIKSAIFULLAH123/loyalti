<?php

namespace MetaFox\Group\Policies;

use MetaFox\Group\Models\ExampleRule as Resource;
use MetaFox\Group\Policies\Contracts\ExampleRulePolicyInterface;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class ExampleRulePolicy.
 * @ignore
 */
class ExampleRulePolicy implements ExampleRulePolicyInterface
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
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function update(User $user): bool
    {
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
