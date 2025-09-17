<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\HasResourceCategory;

/**
 * Class CategoryScope.
 */
class CategoryScope extends BaseScope
{
    /**
     * @var array<int, int>
     */
    private array $categories;

    /**
     * @return int[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param int[] $categories
     *
     * @return CategoryScope
     */
    public function setCategories(array $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($model instanceof HasResourceCategory) {
            $this->applyCategoryDataScope($builder, $model);

            return;
        }

        $this->applyCategoryIdScope($builder, $model);
    }

    /**
     * applyCategoryIdScope.
     *
     * @param Builder $builder
     * @return void
     */
    protected function applyCategoryIdScope(Builder $builder, Model $model)
    {
        $table = $model->getTable();
        $builder->whereIn("$table.category_id", $this->getCategories());
    }

    /**
     * applyCategoryDataScope.
     *
     * @param Builder $builder
     * @param Model   $model
     * @return void
     */
    protected function applyCategoryDataScope(Builder $builder, Model $model)
    {
        if (!$model instanceof HasResourceCategory) {
            return;
        }

        $contentTable = $model->getTable();

        $categoryDataTable = $model->categories()->getTable();

        $categories = $this->getCategories();

        $builder->join($categoryDataTable, "$categoryDataTable.item_id", '=', "$contentTable.id")
            ->whereIn("{$categoryDataTable}.category_id", $categories);
    }
}
