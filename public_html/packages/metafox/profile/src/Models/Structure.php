<?php

namespace MetaFox\Profile\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Profile\Database\Factories\StructureFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * class Structure.
 * @mixin Builder
 *
 * @property int $id
 * @property int $section_id
 * @property int $profile_id
 * @method   static StructureFactory factory(...$parameters)
 */
class Structure extends Pivot implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'user_custom_structure';

    protected $table      = 'user_custom_structure';
    public    $timestamps = false;
    protected $foreignKey = 'section_id';
    protected $relatedKey = 'profile_id';

    /** @var string[] */
    protected $fillable = [
        'profile_id',
        'section_id',
    ];

    /**
     * @return StructureFactory
     */
    protected static function newFactory()
    {
        return StructureFactory::new();
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'id');
    }

}

// end
