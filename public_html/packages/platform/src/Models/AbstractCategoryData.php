<?php

namespace MetaFox\Platform\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

abstract class AbstractCategoryData extends Pivot
{
    protected static function booted()
    {
        static::created(function (self $model) {
            $category = $model->category;

            do {
                $category?->incrementTotalItem();
                $category = $category?->parentCategory;
            } while ($category);
        });

        static::deleted(function (self $model) {
            $category = $model->category;

            do {
                $category?->decrementTotalItem();
                $category = $category?->parentCategory;
            } while ($category);
        });
    }
}
