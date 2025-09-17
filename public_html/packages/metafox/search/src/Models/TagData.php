<?php

namespace MetaFox\Search\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Hashtag\Models\Tag;

/**
 * Class HashtagData.
 *
 * @property int    $id
 * @property int    $tag_id
 * @property int    $item_id
 * @property string $item_type
 * @property Tag    $tag
 */
class TagData extends Pivot
{
    /**
     * @var bool
     */
    public $timestamps = false;

    protected $table = 'search_tag_data';

    /** @var string[] */
    protected $fillable = [
        'tag_id',
        'item_id',
        'item_type',
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'tag_id', 'id');
    }
}
