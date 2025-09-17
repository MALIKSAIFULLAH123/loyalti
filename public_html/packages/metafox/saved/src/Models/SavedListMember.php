<?php

namespace MetaFox\Saved\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Saved\Notifications\AddFriendToListNotification;

/**
 * Class Saved.
 *
 * @mixin Builder
 * @property int       $id
 * @property int       $list_id
 * @property int       $user_id
 * @property string    $user_type
 * @property SavedList $collection
 */
class SavedListMember extends Model implements Entity, IsNotifyInterface
{
    use HasEntity;
    use HasUserMorph;

    public const ENTITY_TYPE = 'saved_list_member';

    protected $table = 'saved_list_members';

    public $timestamps = false;

    protected $fillable = [
        'list_id',
        'user_id',
        'user_type',
    ];

    public function collection(): HasOne
    {
        return $this->hasOne(SavedList::class, 'id', 'list_id');
    }

    public function toNotification(): ?array
    {
        if ($this->collection->userId() == $this->userId()) {
            return null;
        }

        return [$this->user, new AddFriendToListNotification($this)];
    }
}
