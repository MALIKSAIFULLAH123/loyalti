<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasCoverMorph;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasCoverMorphTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class GroupInviteCode.
 *
 * @property int         $id
 * @property mixed       $status
 * @property string      $code
 * @property int         $group_id
 * @property Group       $group
 * @property string|null $expired_at
 */
class GroupInviteCode extends Model implements Entity, HasUrl, HasCoverMorph
{
    use HasUserMorph;
    use HasEntity;
    use HasFactory;
    use HasCoverMorphTrait;

    public const ENTITY_TYPE = 'group_invite_code';

    protected $table = 'group_invite_codes';

    public const STATUS_ACTIVE   = 1;
    public const STATUS_INACTIVE = 0;

    /** @var string[] */
    protected $fillable = [
        'group_id',
        'user_id',
        'user_type',
        'code',
        'expired_at',
        'status',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id')->withTrashed();
    }

    public function getTitleAttribute(): string
    {
        return $this->group->toTitle();
    }

    public function toOGDescription(?User $context = null): ?string
    {
        return $this->group->toOGDescription($context);
    }

    public function getCoversAttribute(): ?array
    {
        return app('storage')->getUrls($this->group->cover_file_id);
    }

    public function isActive(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiFullUrl("group/invite/{$this->code}");
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("group/invite/{$this->code}");
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl("group/invite/{$this->code}");
    }
}

// end
