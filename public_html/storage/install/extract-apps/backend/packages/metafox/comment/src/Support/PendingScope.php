<?php

namespace MetaFox\Comment\Support;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PendingScope.
 */
class PendingScope extends BaseScope
{
    /**
     * @var User
     */
    protected User $user;

    /**
     * PendingScope constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUserContext(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return PendingScope
     */
    public function setUserContext(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();
        $user  = $this->getUserContext();

        if ($user->hasPermissionTo('admincp.has_admin_access')) {
            return;
        }

        $builder->where(function (Builder $whereQuery) use ($table, $user) {
            $whereQuery->where($this->alias($table, 'is_approved'), '=', 1)
                ->orWhere($this->alias($table, 'user_id'), '=', $user->entityId());
        });
    }
}
