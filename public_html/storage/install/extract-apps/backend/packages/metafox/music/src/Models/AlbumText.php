<?php

namespace MetaFox\Music\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class AlbumText.
 */
class AlbumText extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE = 'music_album_text';

    protected $table = 'music_album_text';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'text',
        'text_parsed',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Album::class, 'id', 'id');
    }
}
