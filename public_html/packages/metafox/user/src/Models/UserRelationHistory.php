<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Contracts\User as UserContract;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasFeed;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Profile\Support\Facade\CustomField;
use MetaFox\User\Notifications\UserRelationWithUserNotification;

/**
 * Class UserRelationHistory.
 *
 * @property int               $id
 * @property int               $user_id
 * @property string            $user_type
 * @property int               $relation_id
 * @property int               $relation_with
 * @property UserContract|null $relationWithUser
 * @property UserRelation|null $relationship
 */
class UserRelationHistory extends Model implements Entity, ActivityFeedSource, IsNotifyInterface
{
    use HasEntity;
    use HasUserMorph;
    use HasFeed;

    public const ENTITY_TYPE = 'user_relation_history';

    public $timestamps = false;

    protected $table = 'user_relation_histories';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'relation_id',
        'relation_with',
    ];

    public function owner(): BelongsTo
    {
        return $this->user()->withTrashed();
    }

    public function relationship(): HasOne
    {
        return $this->hasOne(UserRelation::class, 'id', 'relation_id');
    }

    protected function getRelationshipTextAttribute(): ?string
    {
        if (!CustomField::isEnabledRelationshipStatus()) {
            return null;
        }

        $phraseVar = $this?->relationship?->phrase_var;
        if ($phraseVar == null) {
            return null;
        }

        return __p($phraseVar);
    }

    public function relationWithUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'relation_with', 'id')->withTrashed();
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(UserGender::class, 'user_id', 'id');
    }

    /**
     * @return ?FeedAction
     */
    public function toActivityFeed(): ?FeedAction
    {
        return new FeedAction([
            'user_id'       => $this->userId(),
            'user_type'     => $this->userType(),
            'owner_id'      => $this->userId(),
            'owner_type'    => $this->userType(),
            'item_id'       => $this->entityId(),
            'item_type'     => $this->entityType(),
            'type_id'       => User::USER_UPDATE_RELATIONSHIP_ENTITY_TYPE,
            'privacy'       => MetaFoxPrivacy::EVERYONE,
            'from_resource' => 'feed',
        ]);
    }

    public function toNotification(): ?array
    {
        if ($this->relation_with == 0) {
            return null;
        }

        return [$this->relationWithUser, new UserRelationWithUserNotification($this)];
    }
}

// end
