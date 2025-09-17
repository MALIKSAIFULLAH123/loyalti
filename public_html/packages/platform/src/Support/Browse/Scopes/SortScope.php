<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Contracts\HasAlphabetSort;
use MetaFox\Platform\Support\Browse\Contracts\HasFeatureSort;
use MetaFox\Platform\Support\Browse\Contracts\HasTotalMemberSort;

/**
 * Class SortScope.
 */
class SortScope extends BaseScope
{
    public const SORT_DEFAULT      = Browse::SORT_RECENT;
    public const SORT_TYPE_DEFAULT = Browse::SORT_TYPE_DESC;

    /**
     * @param string|null $sort
     * @param string|null $sortType
     */
    public function __construct(?string $sort = null, ?string $sortType = null)
    {
        $this->sort     = $sort ?? self::SORT_DEFAULT;
        $this->sortType = $sortType ?? self::SORT_TYPE_DEFAULT;
    }

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
            Browse::SORT_MOST_DISCUSSED,
            Browse::SORT_MOST_VIEWED,
            Browse::SORT_MOST_LIKED,
            Browse::SORT_LATEST,
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
     * @var string
     */
    private string $sort = self::SORT_DEFAULT;

    /**
     * @var string
     */
    private string $sortType = self::SORT_TYPE_DEFAULT;

    /**
     * @return string
     */
    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     *
     * @return self
     */
    public function setSort(string $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortType(): string
    {
        return $this->sortType;
    }

    /**
     * @param string $sortType
     *
     * @return self
     */
    public function setSortType(string $sortType): self
    {
        $this->sortType = $sortType;

        return $this;
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

        // @todo HasTotalView, HasTotalLike v.v... interfaces.

        switch ($sort) {
            case Browse::SORT_MOST_VIEWED:
                $builder->orderBy($this->alias($table, 'total_view'), $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case Browse::SORT_MOST_LIKED:
                $builder->orderBy($this->alias($table, 'total_like'), $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case Browse::SORT_MOST_DISCUSSED:
                $builder->orderBy($this->alias($table, 'total_comment'), $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case Browse::SORT_RECENT:
            case Browse::SORT_LATEST:
                // use one of theme to speed up performance.
                if (Schema::hasColumn($table, 'created_at')) {
                    $builder->orderBy($this->alias($table, 'created_at'), $sortType);
                    break;
                }

                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
        }

        if ($this instanceof HasAlphabetSort) {
            switch ($sort) {
                case Browse::SORT_A_TO_Z:
                    $this->setSortType(Browse::SORT_TYPE_ASC);
                    $this->applyAlphabetSort($builder, $model);
                    break;
                case Browse::SORT_Z_TO_A:
                    $this->setSortType(Browse::SORT_TYPE_DESC);
                    $this->applyAlphabetSort($builder, $model);
                    break;
            }
        }

        if ($this instanceof HasTotalMemberSort) {
            if ($sort == $this->getTotalMemberSort()) {
                $this->applyTotalMemberSort($builder, $model);
            }
        }

        if ($this instanceof HasFeatureSort) {
            if ($sort == $this->getFeatureSort()) {
                $this->applyFeatureSort($builder, $model);
            }
        }
    }
}
