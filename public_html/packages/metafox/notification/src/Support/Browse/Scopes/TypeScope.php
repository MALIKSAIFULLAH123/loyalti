<?php

namespace MetaFox\Notification\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Notification\Models\Type;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;

/**
 * Class TypeScope.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TypeScope extends BaseScope
{
    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->join('notification_types', function (JoinClause $query) {
            $query->on('notification_types.type', '=', 'notifications.type');
            $query->addScope(resolve(PackageScope::class, [
                'table' => resolve(Type::class)->getTable(),
            ]));
        });
    }

    /**
     * @param QueryBuilder $builder
     *
     * @return void
     */
    public function applyQueryBuilder(QueryBuilder $builder): void
    {
        $builder->join('notification_types', function (JoinClause $query) {
            $query->on('notification_types.type', '=', 'notifications.type');
            $query->addScope(resolve(PackageScope::class, [
                'table' => resolve(Type::class)->getTable(),
            ]));
        });
    }
}
