<?php

namespace MetaFox\Group\Repositories;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupHistory;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface GroupHistory
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @method GroupHistory find($id, $columns = ['*'])
 * @method GroupHistory getModel()
 */
interface GroupHistoryRepositoryInterface
{
    /**
     * @param User  $context
     * @param Group $group
     * @param array $attributes
     * @return void
     */
    public function createHistory(User $context, Group $group, array $attributes): void;

    /**
     * @param GroupHistory $model
     * @return void
     */
    public function sentNotification(GroupHistory $model): void;
}
