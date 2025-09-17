<?php

namespace MetaFox\Invite\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class InviteCode
 *
 * @property int    $id
 * @property string $code
 * @property string $expired_at
 * @property int    $is_active
 * @property int    $user_id
 * @property string $user_type
 */
class InviteCode extends Model implements Entity, HasUrl
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'invite_code';

    protected $table = 'invite_codes';

    /** @var string[] */
    protected $fillable = [
        'code',
        'expired_at',
        'user_id',
        'user_type',
        'is_active',
    ];

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("invite/{$this->code}");
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("invite/{$this->code}");
    }

    public function toLinkInvite(): string
    {
        return url_utility()->makeApiFullUrl("invite/ref?invite_code=$this->code");
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl("invite/{$this->code}");
    }
}

// end
