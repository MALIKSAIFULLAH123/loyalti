<?php

namespace MetaFox\BackgroundStatus\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\BackgroundStatus\Database\Factories\StatusBackgroundFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class StatusBackground.
 * @property int    $id
 * @property int    $item_id
 * @property string $item_type
 * @property int    $user_id
 * @property string $user_type
 * @property int    $background_id
 * @property int    $is_active
 *
 * @method static StatusBackgroundFactory factory(...$parameters)
 */
class StatusBackground extends Model
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasItemMorph;

    public const ENTITY_TYPE = 'bgs_status_background';

    protected $table = 'bgs_status_background';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'item_type',
        'user_id',
        'user_type',
        'background_id',
        'is_active',
    ];

    /**
     * @return StatusBackgroundFactory
     */
    protected static function newFactory(): StatusBackgroundFactory
    {
        return StatusBackgroundFactory::new();
    }

    public function background(): BelongsTo
    {
        return $this->belongsTo(BgsBackground::class, 'background_id', 'id');
    }
}
