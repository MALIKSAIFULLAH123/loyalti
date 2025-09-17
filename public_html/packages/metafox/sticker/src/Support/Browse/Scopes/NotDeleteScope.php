<?php

namespace MetaFox\Sticker\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class NotDeleteScope.
 * @ignore
 * @codeCoverageIgnore
 */
class NotDeleteScope extends BaseScope
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('is_deleted', 0)
            ->orderBy('ordering')
            ->orderBy('id');
    }
}
