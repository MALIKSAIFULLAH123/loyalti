<?php

namespace MetaFox\Ban\Models;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Ban\Supports\Constants;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class BanRule.
 *
 * @property int         $id
 * @property string      $find_value
 * @property string      $type_id
 * @property string|null $replacement
 * @property int         $ban_user
 * @property int         $day_banned
 * @property bool        $is_active
 * @property int|null    $return_user_group
 * @property array       $user_group_effected
 * @property string|null $reason
 * @property string      $created_at
 * @property string      $updated_at
 */
class BanRule extends Model implements Entity
{
    use HasEntity;
    use HasUserMorph;

    public const ENTITY_TYPE = 'user_ban_rule';

    protected $table = 'user_ban_rules';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'type_id',
        'is_active',
        'find_value',
        'replacement',
        'ban_user',
        'day_banned',
        'return_user_group',
        'user_group_effected',
        'reason',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'user_group_effected' => 'array',
    ];

    public function isIpAddressType(): bool
    {
        return $this->type_id === Constants::BAN_IP_ADDRESS_TYPE;
    }

    public function isEmailType(): bool
    {
        return $this->type_id === Constants::BAN_EMAIL_TYPE;
    }
}
