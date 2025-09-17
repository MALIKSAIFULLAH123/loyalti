<?php

namespace MetaFox\EMoney\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class GeneralScope extends BaseScope
{
    public function __construct(private ?string $fromDate = null, private ?string $toDate = null, private ?string $status = null)
    {
    }

    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        if ($this->fromDate) {
            $builder->where(sprintf('%s.created_at', $table), '>=', $this->fromDate);
        }

        if ($this->toDate) {
            $builder->where(sprintf('%s.created_at', $table), '<=', $this->toDate);
        }

        if ($this->status) {
            $builder->where(sprintf('%s.status', $table), $this->status);
        }
    }
}
