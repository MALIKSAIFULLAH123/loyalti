<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Exception;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Class BaseScope.
 */
abstract class BaseScope implements Scope
{
    /**
     * @param string $table
     * @param string $column
     *
     * @return string
     */
    protected function alias(string $table, string $column): string
    {
        return sprintf('%s.%s', $table, $column);
    }

    public function applyQueryBuilder(QueryBuilder $builder): void
    {
        throw new Exception('method applyQueryBuilder need to be override!');
    }

    /**
     * return the case-insensitive LIKE operator bases on the current database driver.
     *
     * @return string
     */
    public function likeOperator(): string
    {
        return database_driver() == 'pgsql' ? 'ilike' : 'like';
    }
}
