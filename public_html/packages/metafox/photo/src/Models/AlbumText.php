<?php

namespace MetaFox\Photo\Models;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AlbumText.
 * @property ?string $text
 * @property ?string $text_parsed
 * @property ?string $description
 */
class AlbumText extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE = 'photo_album_text';

    protected $table = 'photo_album_text';

    protected $fillable = ['id', 'text', 'text_parsed'];

    protected $appends = ['description'];

    public $incrementing = false;

    public $timestamps = false;

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Album::class, 'id', 'id');
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        return '';
    }

    public function getAdminEditUrlAttribute(): string
    {
        return '';
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->text;
    }
}
