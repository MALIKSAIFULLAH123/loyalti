<?php

namespace MetaFox\Music\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class PlaylistData.
 *
 * @property int      $song_id
 * @property int      $playlist_id
 * @property Playlist $playlist
 * @property Song     $item
 */
class PlaylistData extends Pivot
{
    use HasEntity;

    public const ENTITY_TYPE = 'music_playlist_data';

    protected $table = 'music_playlist_data';

    public $timestamps = true;

    protected $primaryKey = 'id';

    protected $fillable = [
        'item_id',
        'playlist_id',
        'ordering',
        'created_at',
        'updated_at',
    ];

    protected static function booted()
    {
        static::created(function (self $model) {
            $duration = $model->item->duration;

            if ($model->playlist instanceof Playlist) {
                $model->playlist->incrementAmount('total_track');
                $model->playlist->incrementAmount('total_length', $duration);
            }
        });

        static::deleted(function (self $model) {
            $duration = $model->item->duration;

            if ($model->playlist instanceof Playlist) {
                $model->playlist->decrementAmount('total_track');
                $model->playlist->decrementAmount('total_length', $duration);
            }
        });
    }

    public function playlist(): BelongsTo
    {
        return $this->belongsTo(Playlist::class, 'playlist_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Song::class, 'item_id');
    }
}
