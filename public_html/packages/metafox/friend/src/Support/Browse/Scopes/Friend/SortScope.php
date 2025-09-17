<?php

namespace MetaFox\Friend\Support\Browse\Scopes\Friend;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as BaseScope;

class SortScope extends BaseScope
{
    public const SORT_DEFAULT      = self::SORT_FULL_NAME;
    public const SORT_TYPE_DEFAULT = Browse::SORT_TYPE_ASC;

    public const SORT_FULL_NAME = 'full_name';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return Arr::pluck(self::getSortOptions(), 'value');
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function getSortOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.sort.recent'),
                'value' => Browse::SORT_RECENT,
            ],
        ];
    }

    /**
     * @var string
     */
    private string $sort = self::SORT_DEFAULT;

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
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        $sort     = $this->getSort();
        $sortType = $this->getSortType();

        switch ($sort) {
            case self::SORT_FULL_NAME:
                $userNameColumn = $this->alias($table, 'user_name');
                $fullNameColumn = $this->alias($table, 'full_name');
                $builder->orderBy(DB::raw("CASE WHEN {$fullNameColumn} = '' THEN {$userNameColumn} ELSE {$fullNameColumn} END"), $sortType)
                    ->orderBy('friends.id', $sortType);
                break;
            case Browse::SORT_RECENT:
                $builder->orderBy('friends.created_at', $sortType)
                    ->orderBy('friends.id', $sortType);
                break;
        }
    }
}
