<?php

namespace MetaFox\Marketplace\Support\Browse\Scopes\Listing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as BaseScope;

/**
 * Class SortScope.
 */
class SortScope extends BaseScope
{
    public const SORT_HIGHEST_PRICE = 'highest_price';
    public const SORT_LOWEST_PRICE  = 'lowest_price';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return array_merge(parent::getAllowSort(), [
            self::SORT_HIGHEST_PRICE,
            self::SORT_LOWEST_PRICE,
        ]);
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function apply(Builder $builder, Model $model)
    {
        $table      = $model->getTable();
        $sort       = $this->getSort();

        switch ($sort) {
            case self::SORT_HIGHEST_PRICE:
                $this->buildSortPrice($builder, $table, Browse::SORT_TYPE_DESC);
                break;
            case self::SORT_LOWEST_PRICE:
                $this->buildSortPrice($builder, $table, Browse::SORT_TYPE_ASC);
                break;
            default:
                parent::apply($builder, $model);
                break;
        }
    }

    protected function buildSortPrice(Builder $builder, string $table, string $sortType)
    {
        $field = sprintf('%s.%s', 'marketplace_listing_prices', 'price');

        $builder
            ->orderByDesc(DB::raw("CASE WHEN {$field} IS NULL THEN 1 ELSE 2 END"))
            ->orderBy($field, $sortType)
            ->orderByDesc($this->alias($table, 'id'));
    }
}
