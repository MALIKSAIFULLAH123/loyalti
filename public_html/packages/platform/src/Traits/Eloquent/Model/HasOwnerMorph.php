<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Traits\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\User\Models\UserEntity;

/**
 * Trait HasOwnerMorph.
 *
 * @mixin HasRelationships
 * @property string          $owner_type
 * @property int             $owner_id
 * @property User|null       $owner
 * @property UserEntity|null $ownerEntity
 */
trait HasOwnerMorph
{
    public function ownerType(): string
    {
        return $this->owner_type;
    }

    public function ownerId(): int
    {
        return $this->owner_id;
    }

    /**
     * @return MorphTo
     */
    public function owner()
    {
        return $this->morphTo('owner', 'owner_type', 'owner_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function ownerEntity()
    {
        return $this->belongsTo(UserEntity::class, 'owner_id', 'id')->withTrashed();
    }

    /**
     * check if $user is the owner of entity.
     *
     * @param  User $user
     * @return bool
     */
    public function isOwner(User $user): bool
    {
        return $user->entityId() == $this->ownerId();
    }

    public function getOwnerAttribute()
    {
        return LoadReduce::getEntity($this->ownerType(), $this->ownerId(), fn () => $this->getRelationValue('owner'));
    }

    public function getOwnerEntityAttribute()
    {
        return LoadReduce::getEntity('user_entity', $this->ownerId(), fn () => $this->getRelationValue('ownerEntity'));
    }
}
