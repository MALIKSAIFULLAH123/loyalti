<?php

namespace MetaFox\Subscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class SubscriptionPendingRegistrationUser.
 *
 * @property int $id
 */
class SubscriptionPendingRegistrationUser extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'subscription_pending_registration_user';

    protected $table = 'subscription_pending_registration_users';

    /** @var string[] */
    protected $fillable = [
        'invoice_id',
        'user_id',
        'user_type',
        'created_at',
    ];

    public $timestamps = false;
}
