<?php

namespace MetaFox\Music\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MetaFox\Localize\Support\Traits\TranslatableCategory;
use MetaFox\Music\Database\Factories\GenreFactory;
use MetaFox\Music\Support\Facades\Music;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Genre.
 *
 * @property int    $id
 * @property string $name
 * @property mixed  $total_album
 * @property mixed  $total_track
 * @property mixed  $total_playlist
 * @property mixed  $level
 * @property mixed  $total_item
 * @property mixed  $name_url
 * @property mixed  $ordering
 * @property mixed  $is_active
 * @property mixed  $parent_id
 * @property Genre  $subCategories
 * @property Genre  $parentCategory
 * @property bool   $is_default
 */
class Genre extends Model implements Entity
{
    use HasEntity;
    use HasAmountsTrait;
    use SoftDeletes;
    use TranslatableCategory;

    public const ENTITY_TYPE = 'music_genre';

    protected $table = 'music_genres';

    protected $fillable = [
        'name',
        'total_album',
        'total_track',
        'total_playlist',
        'level',
        'total_item',
        'name_url',
        'ordering',
        'is_active',
        'parent_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'total_track' => 'integer',
        'total_album' => 'integer',
    ];

    public $timestamps = false;

    protected static function newFactory()
    {
        return GenreFactory::new();
    }

    public function subCategories(): HasMany
    {
        $relation = $this->hasMany(self::class, 'parent_id', 'id');
        $relation->getQuery()->whereNot('id', $this->id);

        return $relation;
    }

    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(
            Song::class,
            'music_genre_data',
            'genre_id',
            'item_id'
        )->using(GenreData::class)->where('item_type', 'music_song');
    }

    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(
            Album::class,
            'music_genre_data',
            'genre_id',
            'item_id'
        )->using(GenreData::class)->where('item_type', 'music_album');
    }

    public function parentCategory(): BelongsTo
    {
        $relation = $this->belongsTo(self::class, 'parent_id', 'id');
        $relation->getQuery()->whereNot('id', $this->id);

        return $relation;
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('music/search?' . http_build_query([
                'entity_type' => Music::convertEntityType(Song::ENTITY_TYPE),
                'genre_id'    => $this->entityId(),
                'view'        => Browse::VIEW_SEARCH,
            ]));
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('music/search?' . http_build_query([
                'entity_type' => Music::convertEntityType(Song::ENTITY_TYPE),
                'genre_id'    => $this->entityId(),
                'view'        => Browse::VIEW_SEARCH,
            ]));
    }

    public function getIsDefaultAttribute(): bool
    {
        return $this->entityId() == Settings::get('music.music_song.song_default_genre', 0);
    }

    public function toAdmincpSubLink(): string
    {
        return url_utility()->makeApiUrl(sprintf('music/genre/%s/genre/browse', $this->entityId()));
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        if (!$this->parent_id) {
            return '/music/genre/browse';
        }

        return sprintf('/music/genre/%s/genre/browse?parent_id=%s', $this->parent_id, $this->parent_id);
    }
}
