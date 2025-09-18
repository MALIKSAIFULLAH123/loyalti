<?php

namespace MetaFox\Invite\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Invite\Database\Factories\InviteFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Invite.
 *
 * @property        int         $id
 * @property        int         $status_id
 * @property        string|null $email
 * @property        string|null $phone_number
 * @property        string|null $message
 * @property        string      $code
 * @property        string      $invite_code
 * @property        string|null $expired_at
 * @property        string      $created_at
 * @property        string      $updated_at
 * @method   static InviteFactory factory(...$parameters)
 */
class Invite extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;

    public const ENTITY_TYPE       = 'invite';
    public const INVITE_PENDING    = 0;
    public const INVITE_COMPLETED  = 1;
    public const INVITE_CANCELLED  = 2;
    public const STATUS_CANCELLED  = 'cancel';
    public const STATUS_COMPLETED  = 'complete';
    public const STATUS_PENDING    = 'pending';
    public const ACTION_CREATE     = 'create';
    public const ACTION_COMPLETED  = 'completed';
    public const INVITE_KEY_FORMAT = '%s_%s_%s';

    protected $table      = 'invites';
    protected $primaryKey = 'id';

    /** @var string[] */
    protected $fillable = [
        'status_id',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'email',
        'phone_number',
        'message',
        'code',
        'invite_code',
        'expired_at',
    ];

    /**
     * @return InviteFactory
     */
    protected static function newFactory()
    {
        return InviteFactory::new();
    }

    public function isPending(): bool
    {
        return $this->status_id == self::INVITE_PENDING;
    }

    public function toLinkInvite(): string
    {
        return url_utility()->makeApiFullUrl("invite/ref?code=$this->code");
    }

    public function ownerType(): string
    {
        return $this->userType();
    }

    public function ownerId(): int
    {
        return $this->owner_id ?? 0;
    }
}

// end
