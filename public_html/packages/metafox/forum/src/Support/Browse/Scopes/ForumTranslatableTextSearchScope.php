<?php

namespace MetaFox\Forum\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * Class ForumTranslatableTextSearchScope.
 */
class ForumTranslatableTextSearchScope extends SearchScope
{
    private string $locale = 'en';

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $this->getTable();

        $locale = $this->getLocale();

        $search = $this->getSearchText();

        if ($table == null) {
            $table = $model->getTable();
        }

        $builder->leftJoin('phrases as ps', function (JoinClause $leftJoin) use ($locale, $table) {
            $leftJoin->on("$table.title", '=', 'ps.key')->where('ps.locale', $locale);
        })->where(function (Builder $q) use ($search, $table) {
            $q->where(function (Builder $subWhere) use ($search, $table) {
                $subWhere->where("$table.title", $this->likeOperator(), "%$search%")
                    ->whereNull('ps.id');
            })->orWhere(fn (Builder $subWhere) => $subWhere->where(function (Builder $builder) use ($search) {
                $builder->whereNotNull('ps.id')
                    ->where('ps.text', $this->likeOperator(), "%$search%");
            }));
        });
    }

    public function applyQueryBuilder(QueryBuilder $builder): void
    {
        $table  = $this->getTable();
        $locale = $this->getLocale();
        $search = $this->getSearchText();

        $builder->leftJoin('phrases as ps', function (JoinClause $leftJoin) use ($locale, $table) {
            $leftJoin->on("$table.title", '=', 'ps.key')->where('ps.locale', $locale);

            return $leftJoin;
        })->where(function (Builder $q) use ($search, $table) {
            $q->where(function (Builder $subWhere) use ($search, $table) {
                $subWhere->where("$table.title", $this->likeOperator(), "%$search%")
                    ->whereNull('ps.id');
            })->orWhere(fn (Builder $subWhere) => $subWhere->where(function (Builder $builder) use ($search) {
                $builder->whereNotNull('ps.id')
                    ->where('ps.text', $this->likeOperator(), "%$search%");
            }));
        });
    }
}
