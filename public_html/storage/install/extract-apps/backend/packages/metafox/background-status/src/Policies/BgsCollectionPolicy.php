<?php

namespace MetaFox\BackgroundStatus\Policies;

use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Policies\Contracts\BgsCollectionPolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class BgsCollectionPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class BgsCollectionPolicy implements BgsCollectionPolicyInterface
{
    use HasPolicyTrait;

    protected string $type = BgsCollection::class;

    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function view(User $user, BgsCollection $resource): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user): bool
    {
        return true;
    }

    public function delete(User $user): bool
    {
        return true;
    }
}
