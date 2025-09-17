<?php

namespace MetaFox\Forum\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope as MainScope;

/**
 * Class PrivacyScope.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class PrivacyScope extends MainScope
{
    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $userId              = $this->getUserId();
        $ownerId             = $this->getOwnerId();
        $table               = $model->getTable();
        $resourceUserColumn  = $table . '.user_id';
        $resourceOwnerColumn = $table . '.owner_id';

        // Resources post by blocked users.
        $builder->leftJoin('user_blocked as blocked_owner', function (JoinClause $join) use ($resourceUserColumn, $userId) {
            $join->on('blocked_owner.owner_id', '=', $resourceUserColumn)
                ->where('blocked_owner.user_id', '=', $userId);
        })->whereNull('blocked_owner.owner_id');

        // Resources post by users blocked you.
        $builder->leftJoin('user_blocked as blocked_user', function (JoinClause $join) use ($resourceUserColumn, $userId) {
            $join->on('blocked_user.user_id', '=', $resourceUserColumn)
                ->where('blocked_user.owner_id', '=', $userId);
        })->whereNull('blocked_user.user_id');

        // Resources post on users blocked you.
        $builder->leftJoin(
            'user_blocked as blocked_on_user',
            function (JoinClause $join) use ($resourceOwnerColumn, $userId) {
                $join->on('blocked_on_user.user_id', '=', $resourceOwnerColumn)
                    ->where('blocked_on_user.owner_id', '=', $userId);
            }
        )->whereNull('blocked_on_user.user_id');

        if ($ownerId !== null) {
            $builder->where($resourceOwnerColumn, $ownerId);
        }
    }
}
