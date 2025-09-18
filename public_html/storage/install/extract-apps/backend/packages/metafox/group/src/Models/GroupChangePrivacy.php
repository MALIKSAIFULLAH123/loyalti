<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class GroupChangePrivacy.
 *
 * @property int    $id
 * @property Group  $group
 * @property int    $privacy
 * @property int    $group_id
 * @property int    $privacy_type
 * @property int    $privacy_item
 * @property string $expired_at
 * @property string $created_at
 * @property Member $members
 */
class GroupChangePrivacy extends Model implements Entity, IsNotifyInterface
{
    use HasEntity;
    use HasUserMorph;

    public const IS_ACTIVE = 1;
    public const IS_NOT_ACTIVE = 0;
    public const ENTITY_TYPE = 'group_change_privacy';

    protected $table = 'group_changed_privacy';

    /** @var string[] */
    protected $fillable = [
        'group_id',
        'user_id',
        'user_type',
        'privacy',
        'privacy_type',
        'privacy_item',
        'expired_at',
        'is_active',
    ];

    public function toNotification(): ?array
    {
        return null;
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id')->withTrashed();
    }

    public function isActive(): int
    {
        return self::IS_ACTIVE;
    }

    public function isNotActive(): int
    {
        return self::IS_NOT_ACTIVE;
    }
}

// end
