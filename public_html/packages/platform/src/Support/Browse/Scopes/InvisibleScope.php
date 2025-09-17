<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FeaturedScopeFeaturedScope.
 */
class InvisibleScope extends BaseScope
{
    protected ?int $isInvisible = null;

    /**
     * InvisibleScope constructor.
     *
     * @param int|null $isInvisible
     */
    public function __construct(?int $isInvisible = null)
    {
        $this->isInvisible = $isInvisible;
    }

    /**
     * @param  int|null $isInvisible
     * @return $this
     */
    public function setIsInvisible(?int $isInvisible): static
    {
        $this->isInvisible = $isInvisible;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getIsInvisible(): ?int
    {
        return $this->isInvisible;
    }

    public function apply(Builder $builder, Model $model)
    {
        $table       = $model->getTable();
        $isInvisible = $this->getIsInvisible();

        if (null === $isInvisible) {
            return;
        }

        $builder->where($this->alias($table, 'is_invisible'), '=', $isInvisible);
    }
}
