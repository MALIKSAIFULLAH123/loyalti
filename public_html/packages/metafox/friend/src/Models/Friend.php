<?php

namespace MetaFox\Friend\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsActivitySubscriptionInterface;
use MetaFox\Platform\Contracts\IsPrivacyItemInterface;
use MetaFox\Platform\Contracts\Membership;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasFeed;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class Friend.
 * @mixin Builder
 * @property int    $id
 * @property int    $user_id
 * @property string $user_type
 * @property int    $owner_id
 * @property User   $owner
 * @property int    $owner_type
 * @method   static FriendFactory factory()
 */
class Friend extends Model implements Membership, IsActivitySubscriptionInterface, IsPrivacyItemInterface, ActivityFeedSource, HasUrl
{
    use HasEntity;
    use HasFeed;
    use HasUserMorph;
    use HasOwnerMorph;
    use HasFactory;

    public const ENTITY_TYPE = 'friend';
    public const PRIVACY_FRIENDS = 'user_friends';

    protected $fillable = ['user_id', 'user_type', 'owner_id', 'owner_type'];

    public function toActivitySubscription(): array
    {
        return [$this->userId(), $this->ownerId()];
    }

    public function toPrivacyItem(): array
    {
        return [
            [$this->userId(), $this->ownerId(), $this->ownerType(), self::PRIVACY_FRIENDS],
        ];
    }

    protected static function newFactory(): FriendFactory
    {
        return FriendFactory::new();
    }

    public function toActivityFeed(): ?FeedAction
    {
        return new FeedAction([
            'user_id'    => $this->userId(),
            'user_type'  => $this->userType(),
            'owner_id'   => $this->userId(),
            'owner_type' => $this->userType(),
            'item_id'    => $this->entityId(),
            'item_type'  => $this->entityType(),
            'type_id'    => $this->entityType(),
            'privacy'    => MetaFoxPrivacy::ONLY_ME,
        ]);
    }

    public function toLink(): ?string
    {
        if ($this->user instanceof User) {
            return null;
        }

        return $this->user->toLink();
    }

    public function toUrl(): ?string
    {
        if ($this->user instanceof User) {
            return null;
        }

        return $this->user->toUrl();
    }

    public function toRouter(): ?string
    {
        if ($this->user instanceof User) {
            return null;
        }

        return $this->user->toRouter();
    }

    public function privacy(): ?int
    {
        return MetaFoxPrivacy::ONLY_ME;
    }

    public function privacyUserId(): ?int
    {
        return $this->userId();
    }

    public function shouldSubscribe(): bool
    {
        return UserPrivacy::hasAccess($this->user, $this->owner, 'follow.add_follow');
    }
}
