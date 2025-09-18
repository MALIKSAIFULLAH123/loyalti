<?php

namespace MetaFox\Subscription\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope as PlatformSearchScope;

/**
 * Class SearchScope.
 */
class SearchScope extends PlatformSearchScope
{
    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $this->getTable();

        $joinedTable = $this->getJoinedTable();

        $joinedField = $this->getJoinedField();

        if ($table == null) {
            $table = $model->getTable();
        }

        if (null !== $joinedTable && null !== $joinedField) {
            $tableField = $this->getTableField();

            if (null == $tableField) {
                $tableField = $model->getKeyName();
            }

            $builder->join(
                $joinedTable,
                function (JoinClause $joinClause) use ($joinedTable, $joinedField, $table, $tableField) {
                    $joinClause->on($joinedTable . '.' . $joinedField, '=', $table . '.' . $tableField);
                }
            );
        }

        $search = $this->getSearchText();
        $fields = $this->getFields();

        $builder->where(function (Builder $query) use ($table, $fields, $search) {
            foreach ($fields as $field) {
                $column = $field;
                //Support in case search field is from another table
                if (strpos($field, '.') === false) {
                    $column = $this->alias($table, $field);
                }
                $query->orWhere($column, $search);
            }
        });
    }

    public function applyQueryBuilder(QueryBuilder $builder): void
    {
        $table = $this->getTable();

        $joinedTable = $this->getJoinedTable();

        $joinedField = $this->getJoinedField();

        if (null !== $joinedTable && null !== $joinedField) {
            $tableField = $this->getTableField();

            $builder->join(
                $joinedTable,
                function (JoinClause $joinClause) use ($joinedTable, $joinedField, $table, $tableField) {
                    $joinClause->on($joinedTable . '.' . $joinedField, '=', $table . '.' . $tableField);
                }
            );
        }

        $search = $this->getSearchText();

        $fields = $this->getFields();

        $builder->where(function (QueryBuilder $query) use ($table, $fields, $search) {
            foreach ($fields as $field) {
                $column = $field;
                //Support in case search field is from another table
                if (strpos($field, '.') === false) {
                    $column = $this->alias($table, $field);
                }
                $query->orWhere($column, $search);
            }
        });
    }
}
