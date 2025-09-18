<?php

namespace MetaFox\Sticker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Sticker\Database\Factories\StickerUserValueFactory;

/**
 * Class StickerUserValue.
 * @property int        $id
 * @property int        $user_id
 * @property string     $user_type
 * @property int        $set_id
 * @property StickerSet $stickerSet
 * @method   static     StickerUserValueFactory factory(...$parameters)
 */
class StickerUserValue extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'sticker_user_value';

    protected $table = 'sticker_user_values';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'set_id',
        'user_id',
        'user_type',
    ];

    /**
     * @return StickerUserValueFactory
     */
    protected static function newFactory(): StickerUserValueFactory
    {
        return StickerUserValueFactory::new();
    }

    public function stickerSet(): BelongsTo
    {
        return $this->belongsTo(StickerSet::class, 'set_id', 'id');
    }
}
