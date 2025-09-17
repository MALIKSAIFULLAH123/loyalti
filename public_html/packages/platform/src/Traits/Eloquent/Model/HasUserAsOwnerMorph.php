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
use MetaFox\User\Models\UserEntity;

/**
 * Trait HasUserAsOwnerMorph.
 *
 * @property int        $user_id
 * @property string     $user_type
 * @property int        $owner_id
 * @property string     $owner_type
 * @property User       $owner
 * @property UserEntity $ownerEntity
 * @mixin HasRelationships
 */
trait HasUserAsOwnerMorph
{
    /**
     * @return int
     */
    public function ownerId(): int
    {
        return $this->user_id;
    }

    /**
     * @return string
     */
    public function ownerType(): string
    {
        return $this->user_type;
    }

    /**
     * @return MorphTo
     */
    public function owner()
    {
        return $this->morphTo('user', 'user_type', 'user_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function ownerEntity()
    {
        return $this->belongsTo(UserEntity::class, 'user_id', 'id')->withTrashed();
    }
}
