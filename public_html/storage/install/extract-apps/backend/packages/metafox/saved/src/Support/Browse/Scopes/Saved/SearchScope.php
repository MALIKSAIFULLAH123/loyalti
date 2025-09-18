<?php

namespace MetaFox\Saved\Support\Browse\Scopes\Saved;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope as PlatformSearchScope;

class SearchScope extends PlatformSearchScope
{
    protected $additionalPairs = [];

    public function apply(Builder $builder, Model $model)
    {
        $table = $this->getTable();

        $joinedTable = $this->getJoinedTable();

        $joinedField = $this->getJoinedField();

        $additionalPairFields = $this->getAdditionalPairJoinedTableFields();

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
                function (JoinClause $joinClause) use ($joinedTable, $joinedField, $table, $tableField, $additionalPairFields) {
                    $joinClause->on($joinedTable . '.' . $joinedField, '=', $table . '.' . $tableField);
                    if (!empty($additionalPairFields)) {
                        foreach ($additionalPairFields as $additionalPairField) {
                            $joinClause->on($joinedTable . '.' . $additionalPairField[0], '=', $table . '.' . $additionalPairField[1]);
                        }
                    }
                }
            );
        }

        $search = $this->getSearchText();
        $fields = $this->getFields();

        $builder->where(function (Builder $query) use ($table, $fields, $search) {
            foreach ($fields as $field) {
                $column = $field;
                //Support in case search field is from another table
                if (!str_contains($field, '.')) {
                    $column = $this->alias($table, $field);
                }
                $query->orWhere("$column", $this->likeOperator(), '%' . $search . '%');
            }
        });
    }

    public function applyQueryBuilder(QueryBuilder $builder): void
    {
        $table = $this->getTable();

        $joinedTable = $this->getJoinedTable();

        $joinedField = $this->getJoinedField();

        $additionalPairFields = $this->getAdditionalPairJoinedTableFields();

        if (null !== $joinedTable && null !== $joinedField) {
            $tableField = $this->getTableField();

            $builder->join(
                $joinedTable,
                function (JoinClause $joinClause) use ($joinedTable, $joinedField, $table, $tableField, $additionalPairFields) {
                    $joinClause->on($joinedTable . '.' . $joinedField, '=', $table . '.' . $tableField);
                    if (!empty($additionalPairFields)) {
                        foreach ($additionalPairFields as $additionalPairField) {
                            $joinClause->on($joinedTable . '.' . $additionalPairField[0], '=', $table . '.' . $additionalPairField[1]);
                        }
                    }
                }
            );
        }

        $search = $this->getSearchText();

        $fields = $this->getFields();

        $builder->where(function (Builder $query) use ($table, $fields, $search) {
            foreach ($fields as $field) {
                $column = $field;
                //Support in case search field is from another table
                if (!str_contains($field, '.')) {
                    $column = $this->alias($table, $field);
                }
                $query->orWhere("$column", $this->likeOperator(), '%' . $search . '%');
            }
        });
    }

    public function setAdditionalPairJoinedTableFields(array $additionalPairs)
    {
        $this->additionalPairs = $additionalPairs;
    }

    public function getAdditionalPairJoinedTableFields()
    {
        return $this->additionalPairs;
    }
}
