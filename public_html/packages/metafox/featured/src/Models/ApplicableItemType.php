<?php

namespace MetaFox\Featured\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Featured\Database\Factories\ApplicableItemTypeFactory;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class ApplicableItemType
 *
 * @property int $id
 * @property int $package_id
 * @property string $item_type
 * @method static ApplicableItemTypeFactory factory(...$parameters)
 */
class ApplicableItemType extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'featured_applicable_item_type';

    protected $table = 'featured_applicable_item_types';

    /** @var string[] */
    protected $fillable = [
        'package_id',
        'item_type'
    ];

    public $timestamps = false;

    /**
     * @return ApplicableItemTypeFactory
     */
    protected static function newFactory()
    {
        return ApplicableItemTypeFactory::new();
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }
}
