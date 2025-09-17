<?php

namespace MetaFox\Blog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Models\AbstractCategoryData;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class CategoryData.
 *
 * @property int      $id
 * @property int      $category_id
 * @property int      $item_id
 * @property Category $category
 *
 * @mixin Builder
 */
class CategoryData extends AbstractCategoryData implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'blog_category_data';

    public $timestamps = false;

    protected $table = 'blog_category_data';

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
