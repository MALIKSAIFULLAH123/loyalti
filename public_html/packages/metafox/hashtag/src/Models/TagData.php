<?php

namespace MetaFox\Hashtag\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Hashtag\Database\Factories\TagDataFactory;

/**
 * Class HashtagData.
 *
 * @property int    $id
 * @property int    $tag_id
 * @property int    $item_id
 * @property string $item_type
 * @property Tag    $tag
 * @method   static TagDataFactory factory(...$parameters)
 */
class TagData extends Pivot
{
    use HasFactory;

    /**
     * @var bool
     */
    public $timestamps = false;

    protected $table = 'hashtag_tag_data';

    /** @var string[] */
    protected $fillable = [
        'tag_id',
        'item_id',
        'item_type',
    ];

    /**
     * @return TagDataFactory
     */
    protected static function newFactory(): TagDataFactory
    {
        return TagDataFactory::new();
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'tag_id', 'id');
    }
}
