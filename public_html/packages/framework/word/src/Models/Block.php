<?php

namespace MetaFox\Word\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Word\Database\Factories\BlockFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Block.
 *
 * @property int    $id
 * @property string $word
 * @property string $created_at
 * @property string $modified_at
 * @property bool   $is_system
 * @method   static BlockFactory factory(...$parameters)
 */
class Block extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'core_word_block';

    protected $table = 'core_word_block';

    /** @var string[] */
    protected $fillable = [
        'word',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * @return BlockFactory
     */
    protected static function newFactory()
    {
        return BlockFactory::new();
    }
}

// end
