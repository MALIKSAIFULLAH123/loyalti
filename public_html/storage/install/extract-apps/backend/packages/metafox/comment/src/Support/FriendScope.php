<?php

namespace MetaFox\Comment\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FriendScope.
 */
class FriendScope extends BaseScope
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

        if (!app_active('metafox/friend')) {
            return;
        }

        $builder->leftJoin('friends AS fr', function (JoinClause $join) use ($table, $user) {
            $join->on('fr.user_id', '=', "{$table}.user_id");
            $join->where('fr.owner_id', $user->entityId());
        });
    }
}
