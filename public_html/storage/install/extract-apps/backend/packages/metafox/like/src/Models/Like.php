<?php

namespace MetaFox\Like\Models;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Like\Database\Factories\LikeFactory;
use MetaFox\Like\Notifications\LikeNotification;
use MetaFox\Platform\Contracts\ActionEntity;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Like.
 * @property        int         $item_id
 * @property        string      $item_type
 * @property        int         $user_id
 * @property        string      $user_type
 * @property        int         $owner_id               - user id of content.
 * @property        string      $owner_type
 * @property        int         $reaction_id
 * @property        User        $user
 * @property        Content     $item
 * @property        Reaction    $reaction
 * @property        string      $created_at
 * @property        string      $updated_at
 * @method   static LikeFactory factory(...$parameters)
 * @mixin Builder
 */
class Like extends Model implements ActionEntity, IsNotifyInterface, HasUrl
{
    use HasEntity;
    use HasItemMorph;
    use HasUserMorph;
    use HasOwnerMorph;
    use HasFactory;

    protected $table = 'likes';

    public const ENTITY_TYPE = 'like';

    protected $fillable = [
        'item_id',
        'item_type',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'reaction_id',
    ];

    public function reaction(): BelongsTo
    {
        return $this->belongsTo(Reaction::class);
    }

    protected static function newFactory(): LikeFactory
    {
        return LikeFactory::new();
    }

    /**
     * @throws AuthenticationException
     */
    public function toNotification(): ?array
    {
        $item   = $this->item;
        $user   = $this->user;
        $owner  = $this->owner;
        $owners = [];

        $pass = app('events')->dispatch('like.owner.notification', [$owner, $item], true);

        $taggedFriends = app('events')->dispatch('friend.get_owner_tag_friends', [$item], true);

        if ($this->userId() != $this->ownerId()) {
            if ($pass || null === $pass) {
                $owners = array_merge($owners, $this->getOwnerNofitiables($user));
            }
        }

        if (!empty($taggedFriends)) {
            foreach ($taggedFriends as $friend) {
                if ($user->entityId() == $friend->owner->entityId()) {
                    continue;
                }

                if ($pass === false && $owner->entityId() == $friend->owner->entityId()) {
                    continue;
                }

                $friendPass = app('events')->dispatch('like.owner.notification', [$friend->owner, $item], true);

                if ($friendPass === false) {
                    continue;
                }

                /**
                 * Like notification only applies for User
                 */
                if ($friend->owner->entityType() != 'user') {
                    continue;
                }

                $owners[] = $friend->owner;
            }
        }

        return [$owners, new LikeNotification($this)];
    }

    protected function getOwnerNofitiables(User $context): array
    {
        if (!$this->owner instanceof HasPrivacyMember) {
            return [$this->owner];
        }

        $notifiables = app('events')->dispatch(
            'friend.mention.notifiables',
            [$context, $this->owner],
            true
        );

        if (!is_array($notifiables)) {
            return [];
        }

        return $notifiables;
    }
    public function toLink(): ?string
    {
        $item = $this->item;

        if (!$item instanceof HasUrl) {
            return null;
        }

        $extra = '';
        if ($item instanceof ActionEntity) {
            $extra = '?' . http_build_query([$this->itemType() => $this->itemId()]);
        }

        return $item->toLink() . $extra;
    }

    public function toUrl(): ?string
    {
        $item = $this->item;

        if (!$item instanceof HasUrl) {
            return null;
        }

        $extra = '';
        if ($item instanceof ActionEntity) {
            $extra = '?' . http_build_query([$this->itemType() => $this->itemId()]);
        }

        return $item->toUrl() . $extra;
    }

    public function toRouter(): ?string
    {
        $item = $this->item;

        if (!$item instanceof HasUrl) {
            return null;
        }

        $extra = '';
        if ($item instanceof ActionEntity) {
            $extra = '?' . http_build_query([$this->itemType() => $this->itemId()]);
        }

        return $item->toRouter() . $extra;
    }
}
