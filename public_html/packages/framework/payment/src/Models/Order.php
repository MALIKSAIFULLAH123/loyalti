<?php

namespace MetaFox\Payment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use MetaFox\Payment\Contracts\IsBillable;
use MetaFox\Payment\Database\Factories\OrderFactory;
use MetaFox\Payment\Support\Payment;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\UserEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Order.
 *
 * @property        int          $id
 * @property        int          $gateway_id
 * @property        int          $user_id
 * @property        string       $user_type
 * @property        int          $item_id
 * @property        string       $item_type
 * @property        string       $title
 * @property        float        $total
 * @property        string       $currency
 * @property        string       $payment_type
 * @property        string       $status
 * @property        string       $recurring_status
 * @property        string       $gateway_order_id
 * @property        string       $gateway_subscription_id
 * @property        string       $created_at
 * @property        string       $updated_at
 * @property        string|null  $payee_type
 * @property        int|null     $payee_id
 * @property        User|null    $payee
 * @property        UserEntity|null $payeeEntity
 * @property        Gateway      $gateway
 * @property        string       $payment_type_text
 * @property        string       $total_text
 * @property        string       $status_text
 * @property        string       $recurring_status_text
 * @method   static OrderFactory factory(...$parameters)
 */
class Order extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasItemMorph;
    use HasUserMorph;

    public const ENTITY_TYPE = 'payment_order';

    public const STATUS_ALL              = 'all';
    public const STATUS_INIT             = 'init';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_PENDING_PAYMENT  = 'pending_payment';
    public const STATUS_COMPLETED        = 'completed';
    public const STATUS_FAILED           = 'failed';

    public const RECURRING_STATUS_UNSET     = 'unset';
    public const RECURRING_STATUS_PENDING   = 'pending';
    public const RECURRING_STATUS_ACTIVE    = 'active';
    public const RECURRING_STATUS_FAILED    = 'failed';
    public const RECURRING_STATUS_ENDED     = 'ended';
    public const RECURRING_STATUS_CANCELLED = 'cancelled';

    public const ALLOW_STATUS = [
        'core::phrase.all'                        => self::STATUS_ALL,
        'payment::phrase.status_init'             => self::STATUS_INIT,
        'payment::phrase.status_pending_approval' => self::STATUS_PENDING_APPROVAL,
        'payment::phrase.status_pending_payment'  => self::STATUS_PENDING_PAYMENT,
        'payment::phrase.status_completed'        => self::STATUS_COMPLETED,
        'payment::phrase.status_failed'           => self::STATUS_FAILED,
    ];

    public const ALLOW_RECURRING_STATUS = [
        'core::phrase.all'                         => self::STATUS_ALL,
        'payment::phrase.recurring_status_pending' => self::RECURRING_STATUS_PENDING,
        'payment::phrase.recurring_status_active'  => self::RECURRING_STATUS_ACTIVE,
        'payment::phrase.status_failed'            => self::RECURRING_STATUS_FAILED,
        'payment::phrase.recurring_status_ended'   => self::RECURRING_STATUS_ENDED,
        'payment::phrase.status_cancelled'          => self::RECURRING_STATUS_CANCELLED,
    ];

    /**
     * contains rules to validate status updating from one to another.
     *
     * @var array<string, mixed>
     */
    protected $statusRules = [
        self::STATUS_PENDING_APPROVAL => [
            self::STATUS_INIT,
        ],
        self::STATUS_PENDING_PAYMENT => [
            self::STATUS_INIT,
            self::STATUS_PENDING_APPROVAL,
        ],
        self::STATUS_COMPLETED => [
            self::STATUS_PENDING_APPROVAL,
            self::STATUS_PENDING_PAYMENT,
        ],
        self::STATUS_FAILED => [
            self::STATUS_PENDING_APPROVAL,
            self::STATUS_PENDING_PAYMENT,
        ],
    ];

    protected $table = 'payment_orders';

    /** @var string[] */
    protected $fillable = [
        'gateway_id',
        'user_id',
        'user_type',
        'item_id',
        'item_type',
        'title',
        'total',
        'currency',
        'payment_type',
        'status',
        'recurring_status',
        'gateway_order_id',
        'gateway_subscription_id',
        'payee_id',
        'payee_type',
    ];

    /**
     * @return OrderFactory
     */
    protected static function newFactory()
    {
        return OrderFactory::new();
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class, 'gateway_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'order_id');
    }

    /**
     * toGatewayOrder.
     *
     * @return ?array<string, mixed>
     */
    public function toGatewayOrder(): ?array
    {
        $item = $this->item;
        $user = $this->user;
        if (!$item instanceof IsBillable || !$user) {
            return null;
        }

        return array_merge($item->toOrder(), [
            'user_title' => $user->toTitle(),
            'email'      => $user->email,
        ]);
    }

    public function isRecurringOrder(): bool
    {
        return $this->payment_type == Payment::PAYMENT_RECURRING;
    }

    public function isStatusInitialized(): bool
    {
        return $this->status == self::STATUS_INIT;
    }

    public function isStatusPendingApproval(): bool
    {
        return $this->status == self::STATUS_PENDING_APPROVAL;
    }

    public function isStatusPendingPayment(): bool
    {
        return $this->status == self::STATUS_PENDING_PAYMENT;
    }

    public function isStatusFailed(): bool
    {
        return $this->status == self::STATUS_FAILED;
    }

    public function isStatusCompleted(): bool
    {
        return $this->status == self::STATUS_COMPLETED;
    }

    public function isRecurringStatusPending(): bool
    {
        return $this->recurring_status == self::RECURRING_STATUS_PENDING;
    }

    public function isRecurringStatusActive(): bool
    {
        return $this->recurring_status == self::RECURRING_STATUS_ACTIVE;
    }

    public function isRecurringStatusFailed(): bool
    {
        return $this->recurring_status == self::RECURRING_STATUS_FAILED;
    }

    public function isRecurringStatusEnded(): bool
    {
        return $this->recurring_status == self::RECURRING_STATUS_ENDED;
    }

    public function isRecurringStatusCancelled(): bool
    {
        return $this->recurring_status == self::RECURRING_STATUS_CANCELLED;
    }

    /**
     * Validate if we can update the current status to the target status.
     *
     * @param  string $targetStatus
     * @return bool
     */
    public function canUpdateToStatus(string $targetStatus): bool
    {
        $statuses = Arr::get($this->statusRules, $targetStatus, []);

        return in_array($this->status, $statuses);
    }

    public function payeeType(): string
    {
        return $this->payee_type;
    }

    public function payeeId(): int
    {
        return $this->payee_id;
    }

    /**
     * @return MorphTo
     */
    public function payee()
    {
        return $this->morphTo('payee', 'payee_type', 'payee_id')->withTrashed();
    }

    public function payeeEntity()
    {
        return $this->belongsTo(UserEntity::class, 'payee_id', 'id')
            ->withTrashed();
    }

    public function getPaymentTypeTextAttribute(): string
    {
        $type = $this->getAttributeFromArray('payment_type');

        return match($type) {
            Payment::PAYMENT_RECURRING => __p('payment::phrase.recurring'),
            Payment::PAYMENT_ONETIME   => __p('payment::phrase.one_time'),
            default                    => __p('core::phrase.n_a'),
        };
    }

    public function getTotalTextAttribute(): string
    {
        return app('currency')->getPriceFormatByCurrencyId($this->getAttributeFromArray('currency'), $this->getAttributeFromArray('total'));
    }

    public function getStatusTextAttribute(): string
    {
        return app('payment.support')->getStatusText($this->getAttributeFromArray('status'));
    }

    public function getRecurringStatusTextAttribute(): ?string
    {
        return app('payment.support')->getRecurringStatusText($this->getAttributeFromArray('recurring_status'));
    }
}
