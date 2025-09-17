<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;

/**
 * Class BoundsScope.
 */
class UserExistScope extends BaseScope
{
    protected string $alias = 'ue';

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @property array<string>
     */
    protected array $onFields = [];

    public function setOnFields(array|string $onFields): self
    {
        if (is_string($onFields)) {
            $onFields = [$onFields];
        }

        $this->onFields = $onFields;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getOnFields(): array
    {
        return $this->onFields;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $alias    = $this->getAlias();
        $onFields = $this->getOnFields();

        $builder->join('user_entities as ' . $alias, function (JoinClause $joinClause) use ($alias, $onFields) {
            foreach ($onFields as $onField) {
                $joinClause->on($alias . '.id', '=', $onField);
            }

            $joinClause->whereNull($alias . '.deleted_at');
        });
    }

    public function applyQueryBuilder(QueryBuilder $builder): void
    {
        $alias    = $this->getAlias();
        $onFields = $this->getOnFields();

        $builder->join('user_entities as ' . $alias, function (JoinClause $joinClause) use ($alias, $onFields) {
            foreach ($onFields as $onField) {
                $joinClause->on($alias . '.id', '=', $onField);
            }

            $joinClause->whereNull($alias . '.deleted_at');
        });
    }
}
