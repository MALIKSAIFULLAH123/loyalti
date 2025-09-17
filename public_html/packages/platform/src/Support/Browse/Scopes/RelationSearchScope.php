<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SearchScope.
 */
class RelationSearchScope extends SearchScope
{
    private string $relation;

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $this->getTable();

        if ($table == null) {
            $table = $model->getTable();
        }

        $search = $this->getSearchText();

        $fields = $this->getFields();

        $builder->whereHas($this->getRelation(), function (Builder $query) use ($table, $fields, $search) {
            foreach ($fields as $field) {
                $column = $field;
                //Support in case search field is from another table
                if (!str_contains($field, '.')) {
                    $column = $this->alias($table, $field);
                }
                $query->where($column, $this->likeOperator(), '%' . $search . '%');
            }
        });
    }

    /**
     * @return string
     */
    public function getRelation(): string
    {
        return $this->relation;
    }

    /**
     * @param  string              $relation
     * @return RelationSearchScope
     */
    public function setRelation(string $relation): self
    {
        $this->relation = $relation;

        return $this;
    }
}
