<?php

namespace MetaFox\Friend\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MetaFox\Friend\Database\Factories\FriendListFactory;
use MetaFox\Platform\Contracts\BigNumberId;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\PrivacyList;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\HasBigNumberId;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserAsOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserEntity;

/**
 * Class FriendList.
 * @mixin Builder
 * @property int           $id
 * @property string        $name
 * @property BelongsToMany $users
 * @property BelongsToMany $userEntities
 * @method   static        FriendListFactory factory()
 */
class FriendList extends Model implements
    PrivacyList,
    BigNumberId,
    HasTitle
{
    use HasEntity;
    use HasUserMorph;
    use HasUserAsOwnerMorph;
    use HasFactory;
    use HasBigNumberId;

    protected $primaryKey = 'id';

    public const ENTITY_TYPE  = 'friend_list';
    public const PRIVACY_TYPE = 'user_friend_list';

    protected $fillable = ['user_id', 'user_type', 'name'];

    public $incrementing = false;

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                User::class,
                'friend_list_data',
                'list_id',
                'user_id',
                'id',
                'id'
            )
            ->using(FriendListData::class);
    }

    /**
     * @return BelongsToMany
     */
    public function userEntities(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                UserEntity::class,
                'friend_list_data',
                'list_id',
                'user_id',
                'id',
                'id'
            )
            ->using(FriendListData::class);
    }

    public function toPrivacyLists(): array
    {
        return [
            [
                'item_id'      => $this->entityId(),
                'item_type'    => $this->entityType(),
                'user_id'      => $this->ownerId(),
                'user_type'    => $this->ownerType(),
                'owner_id'     => $this->ownerId(),
                'owner_type'   => $this->ownerType(),
                'privacy_type' => self::PRIVACY_TYPE,
                'privacy'      => MetaFoxPrivacy::CUSTOM,
            ],
        ];
    }

    /**
     * @return FriendListFactory
     */
    protected static function newFactory(): FriendListFactory
    {
        return FriendListFactory::new();
    }

    public function toTitle(): string
    {
        return $this->name;
    }
}
