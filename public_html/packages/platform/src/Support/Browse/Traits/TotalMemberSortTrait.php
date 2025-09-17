<?php

namespace MetaFox\Platform\Support\Browse\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Contracts\HasTotalMemberSort;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;

/**
 * Trait TotalMemberSortTrait.
 * @mixin HasTotalMemberSort
 * @mixin SortScope
 */
trait TotalMemberSortTrait
{
    /** @var string|null */
    private ?string $table = null;

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
     * @return self
     */
    public function setTable(?string $table = null): self
    {
        $this->table = $table;

        return $this;
    }
    public function applyTotalMemberSort(Builder $builder, Model $model): void
    {
        $table = $model->getTable();

        if ($this->getTable()) {
            $table = $this->getTable();
        }

        $sortType   = $this->getSortType();
        $sortColumn = $this->getTotalMemberSortColumn();

        $builder->orderBy($this->alias($table, $sortColumn), $sortType);
        $builder->orderBy($this->alias($table, 'id'), $sortType);
    }

    public function getTotalMemberSort(): string
    {
        return self::SORT_MOST_MEMBER;
    }
}
