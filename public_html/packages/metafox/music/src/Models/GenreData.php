<?php

namespace MetaFox\Music\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;

/**
 * Class GenreData.
 */
class GenreData extends Pivot
{
    use HasEntity;

    public const ENTITY_TYPE = 'music_genre_data';

    protected $table = 'music_genre_data';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'item_type',
        'genre_id',
    ];

    public $foreignKey = 'genre_id';

    public $relatedKey = 'item_id';

    protected static function booted()
    {
        static::created(function (self $model) {
            if (!$model->genre instanceof Genre) {
                return;
            }

            $parent = $model->genre;

            $field = null;

            if (null !== $model->pivotParent) {
                $field = match ($model->pivotParent->entityType()) {
                    Song::ENTITY_TYPE  => 'total_track',
                    Album::ENTITY_TYPE => 'total_album',
                    default            => null,
                };
            }

            do {
                if ($field) {
                    $parent->incrementAmount($field);
                }
                $parent->incrementAmount('total_item');
                $parent = $parent?->parentCategory;
            } while ($parent);
        });

        static::deleted(function (self $model) {
            if (!$model->genre instanceof Genre) {
                return;
            }

            $parent = $model->genre;

            $field = null;

            if (null !== $model->pivotParent) {
                $field = match ($model->pivotParent->entityType()) {
                    Song::ENTITY_TYPE  => 'total_track',
                    Album::ENTITY_TYPE => 'total_album',
                    default            => null,
                };
            }

            do {
                if ($field) {
                    $parent->decrementAmount($field);
                }
                $parent->decrementAmount('total_item');
                $parent = $parent?->parentCategory;
            } while ($parent);
        });
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class, 'genre_id');
    }
}
