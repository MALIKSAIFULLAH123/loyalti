<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Announcement.
 *
 * @mixin Builder
 * @property int    $id
 * @property int    $group_id
 * @property int    $user_id
 * @property string $user_type
 * @property int    $item_id
 * @property string $item_type
 * @property string $created_at
 * @property string $updated_at
 * @property Group  $group
 */
class Announcement extends Model implements Entity
{
    use HasEntity;
    use HasUserMorph;
    use HasItemMorph;

    public const ENTITY_TYPE = 'group_announcement';

    protected $table = 'group_announcements';

    /** @var string[] */
    protected $fillable = [
        'group_id',
        'user_id',
        'user_type',
        'item_id',
        'item_type',
    ];

    public function group(): HasOne
    {
        return $this->hasOne(Group::class, 'id', 'group_id')->withTrashed();
    }

    public function hidden(): HasMany
    {
        return $this->hasMany(AnnouncementHide::class, 'announcement_id', 'id');
    }

    public function isMarkedRead(User $context): bool
    {
        return $this->hasMany(AnnouncementHide::class, 'announcement_id')
            ->where('user_id', $context->entityId())->exists();
    }
}

// end
