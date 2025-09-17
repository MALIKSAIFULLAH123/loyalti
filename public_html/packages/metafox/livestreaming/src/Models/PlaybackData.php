<?php

namespace MetaFox\LiveStreaming\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\LiveStreaming\Database\Factories\PlaybackDataFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class PlaybackData.
 *
 * @property        int                 $id
 * @method   static PlaybackDataFactory factory(...$parameters)
 */
class PlaybackData extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'livestreaming_playback_data';

    protected $table = 'livestreaming_playback_data';

    /** @var string[] */
    protected $fillable = [
        'live_id',
        'playback_id',
        'privacy',
        'created_at',
        'updated_at',
    ];

    /**
     * @return PlaybackDataFactory
     */
    protected static function newFactory()
    {
        return PlaybackDataFactory::new();
    }

    public function liveVideo(): BelongsTo
    {
        return $this->belongsTo(LiveVideo::class, 'id', 'live_id');
    }
}

// end
