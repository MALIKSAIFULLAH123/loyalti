<?php

namespace MetaFox\Story\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Story\Notifications\StoryReactionNotification;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class StoryReaction
 *
 * @property int        $id
 * @property int        $story_id
 * @property int        $user_id
 * @property string     $user_type
 * @property string     $created_at
 * @property string     $updated_at
 * @property Collection $reactionData
 * @property Story      $story
 */
class StoryReaction extends Model implements Entity, HasUrl, IsNotifyInterface
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'story_reaction';

    protected $table = 'story_reactions';

    /** @var string[] */
    protected $fillable = [
        'story_id',
        'user_id',
        'user_type',
        'created_at',
        'updated_at',
    ];

    public function reactionData(): BelongsTo
    {
        return $this->belongsTo(StoryReactionData::class, 'id', 'story_reaction_id')
            ->where('story_reaction_id', $this->entityId());
    }

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class, 'story_id', 'id');
    }

    public function toNotification(): ?array
    {
        $owner = $this->story->user;

        if ($this->userId() == $owner->entityId()) {
            return null;
        }

        return [$owner, new StoryReactionNotification($this)];
    }

    public function toLink(): ?string
    {
        $story = $this->story;
        return url_utility()->makeApiUrl("{$story->entityType()}/{$story->userId()}/{$story->entityId()}");
    }

    public function toUrl(): ?string
    {
        $story = $this->story;
        return url_utility()->makeApiFullUrl("{$story->entityType()}/{$story->userId()}/{$story->entityId()}");
    }

    public function toRouter(): ?string
    {
        $story = $this->story;
        return url_utility()->makeApiMobileUrl("{$story->entityType()}/{$story->userId()}/{$story->entityId()}");
    }
}

// end
