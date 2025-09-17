<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;

/**
 * Class SearchScope.
 */
class SearchScope extends BaseScope
{
    /** @var string[] */
    private array $fields;

    private string $searchText;

    /** @var string|null */
    private ?string $table = null;

    /**
     * @var string|null
     */
    private ?string $joinedTable = null;

    /**
     * @var string|null
     */
    private ?string $aliasJoinedTable = null;

    /**
     * @var string|null
     */
    private ?string $joinedField = null;

    /**
     * @var string|null
     */
    private ?string $tableField = null;

    /**
     * @var bool
     */
    private bool $useLeftJoin = false;

    /**
     * SearchScope constructor.
     *
     * @param string|null $query
     * @param array       $fields
     * @param string|null $table
     * @param string|null $tableField
     */
    public function __construct(?string $query = null, array $fields = [], ?string $table = null, ?string $tableField = null)
    {
        $this->searchText = $query ?? '';
        $this->fields     = $fields;
        $this->table      = $table;
        $this->tableField = $tableField;
    }

    /**
     * @return string|null
     */
    public function getTable(): ?string
    {
        return $this->table;
    }

    /**
     * @param string|null $table
     *
     * @return SearchScope
     */
    public function setTable(?string $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param string[] $fields
     *
     * @return SearchScope
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchText(): string
    {
        return $this->searchText;
    }

    /**
     * @param string $searchText
     *
     * @return SearchScope
     */
    public function setSearchText(string $searchText): self
    {
        $this->searchText = $searchText;

        return $this;
    }

    /**
     * @param  string $table
     * @return $this
     */
    public function setJoinedTable(string $table): self
    {
        $this->joinedTable = $table;

        return $this;
    }

    /**
     * @param  string $alias
     * @return $this
     */
    public function setAliasJoinedTable(string $alias): self
    {
        $this->aliasJoinedTable = $alias;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getJoinedTable(): ?string
    {
        return $this->joinedTable;
    }

    /**
     * @return string|null
     */
    public function getAliasJoinedTable(): ?string
    {
        return $this->aliasJoinedTable;
    }

    /**
     * @param  string $field
     * @return $this
     */
    public function setJoinedField(string $field): self
    {
        $this->joinedField = $field;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getJoinedField(): ?string
    {
        return $this->joinedField;
    }

    /**
     * @param  string $field
     * @return $this
     */
    public function setTableField(string $field): self
    {
        $this->tableField = $field;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTableField(): ?string
    {
        return $this->tableField;
    }

    public function asLeftJoin(): self
    {
        $this->useLeftJoin = true;

        return $this;
    }

    public function useLeftJoin(): bool
    {
        return $this->useLeftJoin;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $this->getTable();

        $joinedTable = $this->getJoinedTable();

        $joinedField = $this->getJoinedField();

        $aliasJoinedTable = $this->getAliasJoinedTable() ?? $joinedTable;

        if ($table == null) {
            $table = $model->getTable();
        }

        if (null !== $joinedTable && null !== $joinedField) {
            $tableField = $this->getTableField();

            if (null == $tableField) {
                $tableField = $model->getKeyName();
            }

            if ($aliasJoinedTable !== $joinedTable) {
                $joinedTable = "$joinedTable as $aliasJoinedTable";
            }

            $joinVerb = $this->useLeftJoin() ? 'leftJoin' : 'join';

            $builder->$joinVerb(
                $joinedTable,
                function (JoinClause $joinClause) use ($aliasJoinedTable, $joinedField, $table, $tableField) {
                    $joinClause->on($aliasJoinedTable . '.' . $joinedField, '=', $table . '.' . $tableField);
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

        $aliasJoinedTable = $this->getAliasJoinedTable() ?? $joinedTable;

        if (null !== $joinedTable && null !== $joinedField) {
            $tableField = $this->getTableField();

            if ($aliasJoinedTable !== $joinedTable) {
                $joinedTable = "$joinedTable as $aliasJoinedTable";
            }

            $builder->join(
                $joinedTable,
                function (JoinClause $joinClause) use ($aliasJoinedTable, $joinedField, $table, $tableField) {
                    $joinClause->on($aliasJoinedTable . '.' . $joinedField, '=', $table . '.' . $tableField);
                }
            );
        }

        $search = $this->getSearchText();

        $fields = $this->getFields();

        $builder->where(function (QueryBuilder $query) use ($table, $fields, $search) {
            foreach ($fields as $field) {
                $column = $field;
                //Support in case search field is from another table
                if (!str_contains($field, '.')) {
                    $column = $this->alias($table, $field);
                }
                $query->orWhere($column, $this->likeOperator(), '%' . $search . '%');
            }
        });
    }
}
