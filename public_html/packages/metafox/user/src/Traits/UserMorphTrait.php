<?php

namespace MetaFox\User\Traits;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\User as UserModel;

/**
 * @mixin AbstractRepository
 */
trait UserMorphTrait
{
    public function deleteUserData(User $user): void
    {
        $this->lazyDeleteWhere([
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
        ]);
    }

    public function deleteOwnerData(User $user): void
    {
        $this->lazyDeleteWhere([
            'owner_id'   => $user->entityId(),
            'owner_type' => $user->entityType(),
        ]);
    }

    public function lazyDeleteWhere(array $where = []): void
    {
        if (empty($where)) {
            return;
        }

        $query = $this->getModel()->newModelQuery()
            ->where($where);

        foreach ($query->lazy() as $model) {
            if (!$model instanceof Model) {
                continue;
            }

            $model->delete();
        }
    }
}
