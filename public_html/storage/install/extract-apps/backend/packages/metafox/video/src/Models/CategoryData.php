<?php

namespace MetaFox\Video\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Models\AbstractCategoryData;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class CategoryData.
 * @mixin Builder
 * @property int      $category_id
 * @property int      $item_id
 * @property Category $category
 */
class CategoryData extends AbstractCategoryData
{
    use HasEntity;

    public const ENTITY_TYPE = 'video_category_data';

    public $timestamps = false;

    protected $table = 'video_category_data';

    protected $fillable = [
        'category_id',
        'item_id',
    ];
    protected $foreignKey = 'item_id';

    protected $relatedKey = 'category_id';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
