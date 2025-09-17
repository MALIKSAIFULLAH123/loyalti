<?php

namespace MetaFox\Payment\Support\Browse\Scopes\Order;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class StatusScope extends BaseScope
{
    /**
     * @var array<string>
     */
    private array $excludes = [];

    /**
     * @var string|array<string>
     */
    private string|array $status;

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model)
    {
        $table  = $model->getTable();
        $status = $this->getStatus();
        $builder->whereNotIn($this->alias($table, 'status'), $this->getExcludes());

        if (!empty($status)) {
            $builder->whereIn($this->alias($table, 'status'), $this->getStatus());
        }
    }

    /**
     * @param  array<string> $excludes
     * @return StatusScope
     */
    public function exclude(array $excludes): self
    {
        $this->excludes = $excludes;

        return $this;
    }

    /**
     * @param  string|array<string> $status
     * @return StatusScope
     */
    public function setStatus(array|string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getExcludes(): array
    {
        return $this->excludes;
    }

    /**
     * @return array<string>
     */
    public function getStatus(): array
    {
        if (!is_array($this->status)) {
            return [$this->status];
        }

        return $this->status;
    }
}
