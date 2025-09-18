<?php

namespace MetaFox\Sticker\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Sticker\Database\Factories\StickerRecentFactory;

/**
 * Class StickerRecent.
 * @mixin Builder
 *
 * @property int    $id
 * @property int    $sticker_id
 * @property int    $user_id
 * @property string $user_type
 * @property string $created_at
 * @property string $updated_at
 * @method   static StickerRecentFactory factory(...$parameters)
 */
class StickerRecent extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE            = 'sticker_recent';
    public const MAXIMUM_RECENT_STICKER = 50;

    protected $table = 'sticker_recent';

    /** @var string[] */
    protected $fillable = [
        'sticker_id',
        'user_id',
        'user_type',
    ];

    /**
     * @return StickerRecentFactory
     */
    protected static function newFactory(): StickerRecentFactory
    {
        return StickerRecentFactory::new();
    }
}

// end
