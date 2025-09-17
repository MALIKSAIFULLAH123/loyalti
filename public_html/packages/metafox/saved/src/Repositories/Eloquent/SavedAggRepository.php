<?php

namespace MetaFox\Saved\Repositories\Eloquent;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Saved\Models\SavedAgg;
use MetaFox\Saved\Repositories\SavedAggRepositoryInterface;

/**
 * Class SavedAggRepository.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class SavedAggRepository extends AbstractRepository implements SavedAggRepositoryInterface
{
    public function model(): string
    {
        return SavedAgg::class;
    }

    public function deleteForUser(User $user)
    {
        $this->getModel()->newQuery()->where('user_id', $user->entityId())->delete();
    }
}
