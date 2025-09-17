<?php

namespace MetaFox\Friend\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Testing\Fluent\Concerns\Has;
use MetaFox\Friend\Database\Factories\FriendListDataFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\IsPrivacyItemInterface;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class FriendListData.
 *
 * @mixin Builder
 * @property int    $id
 * @property int    $list_id
 * @property int    $user_id
 * @property string $user_type
 * @property int    $ordering
 * @method   static FriendListDataFactory factory()
 */
class FriendListData extends Pivot implements IsPrivacyItemInterface, Entity
{
    use HasFactory;
    use HasEntity;

    const ENTITY_TYPE = 'friend_list_data';

    protected $table = 'friend_list_data';

    public $timestamps = false;

    public function toPrivacyItem(): array
    {
        return [
            [$this->user_id, $this->list_id, FriendList::ENTITY_TYPE, FriendList::PRIVACY_TYPE],
        ];
    }

    protected static function newFactory(): FriendListDataFactory
    {
        return FriendListDataFactory::new();
    }
}
