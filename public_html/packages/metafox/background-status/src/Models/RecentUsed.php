<?php

namespace MetaFox\BackgroundStatus\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\BackgroundStatus\Database\Factories\RecentUsedFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class RecentUsed.
 * @property int    $id
 * @property int    $user_id
 * @property string $user_type
 * @property int    $background_id
 * @property string $created_at
 *
 * @method static RecentUsedFactory factory(...$parameters)
 */
class RecentUsed extends Model
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'bgs_recent_used';

    public const UPDATED_AT = null;

    protected $table = 'bgs_recent_used';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'background_id',
    ];

    /**
     * @return RecentUsedFactory
     */
    protected static function newFactory(): RecentUsedFactory
    {
        return RecentUsedFactory::new();
    }

    public function background(): BelongsTo
    {
        return $this->belongsTo(BgsBackground::class, 'background_id', 'id');
    }
}
