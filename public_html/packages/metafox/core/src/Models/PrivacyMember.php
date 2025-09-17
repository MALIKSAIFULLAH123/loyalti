<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\IsPrivacyMemberInterface;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class PrivacyData.
 * @property int $user_id
 * @property int $privacy_id
 * @property int $id
 * @mixin Builder
 */
class PrivacyMember extends Model implements IsPrivacyMemberInterface, Entity
{
    use HasEntity;
    public const ENTITY_TYPE = 'core_privacy_member';

    protected $table = 'core_privacy_members';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'privacy_id',
    ];

    public function userId()
    {
        return $this->user_id;
    }

    public function privacyId()
    {
        return $this->privacy_id;
    }
}
