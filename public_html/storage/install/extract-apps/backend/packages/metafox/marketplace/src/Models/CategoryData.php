<?php

namespace MetaFox\Marketplace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Models\AbstractCategoryData;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class CategoryData.
 *
 * @property int $id
 * @property int $category_id
 * @property int $item_id
 *
 * @mixin Builder
 */
class CategoryData extends AbstractCategoryData
{
    use HasEntity;

    public const ENTITY_TYPE = 'marketplace_category_data';

    protected $table = 'marketplace_category_data';

    public $timestamps = false;

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
