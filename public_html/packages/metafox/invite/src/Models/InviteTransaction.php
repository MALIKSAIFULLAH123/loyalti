<?php

namespace MetaFox\Invite\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class InviteTransaction.
 *
 * @property int $id
 */
class InviteTransaction extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'invite_apt_transaction';

    protected $table = 'invite_apt_transactions';

    /** @var string[] */
    protected $fillable = [
        'address',
        'action',
    ];
}

// end
