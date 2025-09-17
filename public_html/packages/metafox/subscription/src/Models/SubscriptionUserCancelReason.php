<?php

namespace MetaFox\Subscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Subscription\Database\Factories\SubscriptionUserCancelReasonFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class SubscriptionUserCancelReason.
 *
 * @property int    $id
 * @method   static SubscriptionUserCancelReasonFactory factory(...$parameters)
 */
class SubscriptionUserCancelReason extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'subscription_user_cancel_reason';

    protected $table = 'subscription_user_cancel_reasons';

    /** @var string[] */
    protected $fillable = [
        'invoice_id',
        'reason_id',
        'created_at',
    ];

    public $timestamps = false;

    public function reason(): BelongsTo
    {
        return $this->belongsTo(SubscriptionCancelReason::class, 'reason_id');
    }

    /**
     * @return SubscriptionUserCancelReasonFactory
     */
    protected static function newFactory()
    {
        return SubscriptionUserCancelReasonFactory::new();
    }
}
