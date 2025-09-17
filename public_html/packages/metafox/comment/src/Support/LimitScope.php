<?php

namespace MetaFox\Comment\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class LimitScope extends BaseScope
{
    /**
     * @var int|null
     */
    private ?int $limit;

    /**
     * @param int|null $limit
     */
    public function __construct(?int $limit = null)
    {
        $this->limit = $limit;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param int|null $limit
     *
     * @return self
     */
    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function apply(Builder $builder, Model $model)
    {
        $limit = $this->getLimit();
        if ($limit != null && $limit > 0) {
            $builder->limit($limit);
        }
    }
}
