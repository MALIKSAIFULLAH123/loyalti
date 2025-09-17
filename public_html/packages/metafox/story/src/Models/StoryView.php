<?php

namespace MetaFox\Story\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class StoryView.
 *
 * @property int           $id
 * @property int           $story_id
 * @property int           $user_id
 * @property string        $user_type
 * @property string        $created_at
 * @property string        $updated_at
 * @property Story         $story
 * @property StoryReaction $reaction
 */
class StoryView extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'story_view';

    protected $table = 'story_views';

    /** @var string[] */
    protected $fillable = [
        'story_id',
        'user_id',
        'user_type',
        'created_at',
        'updated_at',
    ];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class, 'story_id', 'id');
    }

    public function reaction(): BelongsTo
    {
        return $this->belongsTo(StoryReaction::class, 'story_id', 'story_id');
    }
}

// end
