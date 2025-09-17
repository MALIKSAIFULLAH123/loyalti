<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Group\Database\Factories\BlockFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Block.
 *
 * @property int    $id
 * @method   static BlockFactory factory(...$parameters)
 * @property Group  $group
 * @property mixed  $group_id
 */
class Block extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;

    public const ENTITY_TYPE = 'group_block';

    protected $table = 'group_blocks';

    /** @var string[] */
    protected $fillable = [
        'id',
        'group_id',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
    ];

    /**
     * @return BlockFactory
     */
    protected static function newFactory()
    {
        return BlockFactory::new();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id')->withTrashed();
    }
}

// end
