<?php

namespace MetaFox\Event\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Models\AbstractCategoryData;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class CategoryData.
 * @property int      $id
 * @property int      $category_id
 * @property int      $item_id
 * @property Category $category
 */
class CategoryData extends AbstractCategoryData implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'event_category_data';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'event_category_data';

    protected $foreignKey = 'item_id';

    protected $relatedKey = 'category_id';

    /**
     * @var string[]
     */
    protected $fillable = [
        'category_id',
        'item_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
