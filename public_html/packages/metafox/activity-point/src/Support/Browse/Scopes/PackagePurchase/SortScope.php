<?php

namespace MetaFox\ActivityPoint\Support\Browse\Scopes\PackagePurchase;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as BaseScope;

/**
 * Class SortScope.
 */
class SortScope extends BaseScope
{
    public const SORT_DEFAULT      = Browse::SORT_RECENT;
    public const SORT_TYPE_DEFAULT = Browse::SORT_TYPE_DESC;
    public const SORT_PACKAGE_NAME = 'package_name';
    public const SORT_POINT        = 'points';
    public const SORT_PRICE        = 'price';

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        return ['sometimes', 'nullable', 'string', 'in:' . implode(',', static::getAllowSort())];
    }

    /**
     * @return string[]
     */
    public static function sortTypes(): array
    {
        return ['sometimes', 'nullable', 'string', 'in:' . implode(',', static::getAllowSortType())];
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return [
            Browse::SORT_RECENT,
            self::SORT_PACKAGE_NAME,
            self::SORT_POINT,
            self::SORT_PRICE,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowSortType(): array
    {
        return [
            Browse::SORT_TYPE_DESC,
            Browse::SORT_TYPE_ASC,
        ];
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        $sort     = $this->getSort();
        $sortType = $this->getSortType();

        switch ($sort) {
            case self::SORT_PACKAGE_NAME:
                $builder->orderBy('apt_packages.title', $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case self::SORT_POINT:
            case self::SORT_PRICE:
                $builder->orderBy($this->alias($table, $sort), $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case Browse::SORT_RECENT:
                if (Schema::hasColumn($table, 'created_at')) {
                    $builder->orderBy($this->alias($table, 'created_at'), $sortType);
                    break;
                }

                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
        }
    }
}
