<?php

namespace MetaFox\SEO\Support\Scopes\Sitemap;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 * @ignore
 * @codeCoverageIgnore
 */
class ViewScope extends BaseScope
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $table     = $model->getTable();

        if ($this->columnExists($table, 'is_aprroved')) {
            $builder->where($this->alias($table, 'is_aprroved'), 1);
        }

        if ($this->columnExists($table, 'in_process')) {
            $builder->where($this->alias($table, 'in_process'), 0);
        }

        if ($this->columnExists($table, 'approve_status')) {
            $builder->where($this->alias($table, 'approve_status'), 'approved');
        }

        if ($this->columnExists($table, 'status')) {
            $builder->where($this->alias($table, 'status'), 'approved');
        }
    }

    protected function columnExists(string $table, string $column): bool
    {
        return (bool) Cache::rememberForever("$table.$column", function () use ($table, $column) {
            return Schema::hasColumn($table, $column);
        });
    }
}
