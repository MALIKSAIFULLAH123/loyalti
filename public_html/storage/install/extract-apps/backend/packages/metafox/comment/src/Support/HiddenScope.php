<?php

namespace MetaFox\Comment\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HiddenScope.
 */
class HiddenScope extends BaseScope
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

        if ($user->hasPermissionTo('comment.moderate')) {
            return;
        }

        $builder->leftJoin('comment_hidden', function (JoinClause $joinClause) use ($table) {
            $joinClause->on('comment_hidden.item_id', '=', "{$table}.user_id")
                ->where('comment_hidden.type', '=', Helper::HIDE_GLOBAL);
        })->where(function (Builder $builder) use ($user) {
            $builder->whereNull('comment_hidden.id')
                ->orWhere('comment_hidden.user_id', '=', $user->entityId());
        });
    }
}
