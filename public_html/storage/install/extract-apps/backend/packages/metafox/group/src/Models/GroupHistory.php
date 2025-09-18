<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class GroupHistory
 *
 * @property int    $id
 * @property int    $group_id
 * @property int    $user_id
 * @property string $user_type
 * @property string $type
 * @property mixed  $extra
 * @property Group  $group
 */
class GroupHistory extends Model implements Entity, IsNotifyInterface, HasUrl
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'group_history';

    protected $table = 'group_histories';

    /** @var string[] */
    protected $fillable = [
        'group_id',
        'user_id',
        'user_type',
        'type',
        'extra',
        'created_at',
        'updated_at',
    ];

    public function toNotification(): ?array
    {
        return null;
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id')->withTrashed();
    }

    public function toLink(): ?string
    {
        return $this->group?->toLink();
    }

    public function toUrl(): ?string
    {
        return $this->group?->toUrl();
    }

    public function toRouter(): ?string
    {
        return $this->group?->toRouter();
    }
}

// end
