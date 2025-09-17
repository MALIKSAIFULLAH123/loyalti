<?php

namespace MetaFox\Photo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Models\AbstractCategoryData;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class CategoryData.
 * @mixin Builder
 * @property int      $category_id
 * @property int      $item_id
 * @property Category $category
 */
class CategoryData extends AbstractCategoryData implements Entity
{
    use HasEntity;

    public $timestamps = false;

    protected $table = 'photo_category_data';

    protected $foreignKey = 'item_id';

    protected $relatedKey = 'category_id';

    protected $fillable = [
        'category_id',
        'item_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
