<?php

namespace MetaFox\Story\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * stub: /packages/models/model_tag_data.stub
 */

/**
 * Class Story
 *
 * @mixin Builder
 * @property int $id
 * @property int $item_id
 * @property int $tag_id
 * @property string $tag_text
 */
class StoryTagData extends Pivot
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'story_tag_data';

    /**
     * @var string[]
     */
    protected $fillable = [
        'item_id',
        'tag_id',
    ];
}
