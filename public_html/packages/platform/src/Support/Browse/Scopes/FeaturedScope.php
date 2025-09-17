<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FeaturedScopeFeaturedScope.
 */
class FeaturedScope extends BaseScope
{
    protected ?int $isFeatured = null;

    /**
     * FeaturedScopeFeaturedScope constructor.
     *
     * @param int|null $isFeatured
     */
    public function __construct(?int $isFeatured = null)
    {
        $this->isFeatured = $isFeatured;
    }

    /**
     * @param  int|null $isFeatured
     * @return $this
     */
    public function setIsFeatured(?int $isFeatured): static
    {
        $this->isFeatured = $isFeatured;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getIsFeatured(): ?int
    {
        return $this->isFeatured;
    }

    public function apply(Builder $builder, Model $model)
    {
        $table      = $model->getTable();
        $isFeatured = $this->getIsFeatured();

        if (null == $isFeatured) {
            return;
        }

        $builder->where($this->alias($table, 'is_featured'), '=', $this->isFeatured);
    }
}
