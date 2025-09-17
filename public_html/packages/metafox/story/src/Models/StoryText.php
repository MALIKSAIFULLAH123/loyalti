<?php

namespace MetaFox\Story\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model_text.stub.
 */

/**
 * Class Story.
 *
 * @property int    $id
 * @property string $text
 * @property string $text_parsed
 *
 * @mixin Builder
 */
class StoryText extends Model implements ResourceText
{
    use HasEntity;

    public $timestamps = false;

    public $incrementing = false;

    public const ENTITY_TYPE = 'story_text';

    /**
     * @var string
     */
    protected $table = 'story_text';

    protected $fillable = [
        'text',
        'text_parsed',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Story::class, 'id', 'id');
    }
}

// end
