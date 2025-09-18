<?php

namespace MetaFox\Subscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Payment\Contracts\IsRecurringlyBillable;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Support\Payment;
use MetaFox\Payment\Support\Payment as PaymentSupport;
use MetaFox\Payment\Traits\RecurringlyBillableTrait;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Subscription\Database\Factories\SubscriptionInvoiceFactory;
use MetaFox\Subscription\Support\Helper;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class SubscriptionInvoice.
 *
 * @property        int                            $id
 * @property        mixed                          $created_at
 * @property        mixed                          $activated_at
 * @property        mixed                          $expired_at
 * @property        string                         $payment_status
 * @property        string                         $renew_type
 * @property        int                            $recurring_price
 * @property        int                            $initial_price
 * @property        SubscriptionPackage            $package
 * @property        Gateway                        $gateway
 * @property        SubscriptionInvoiceTransaction $transactions
 * @property        bool                           $is_canceled_by_gateway
 * @property        bool                           $is_recurring
 * @property        string                         $payment_gateway
 * @property        string                         $expired_date
 * @property        string                         $payment_description
 * @method   static SubscriptionInvoiceFactory     factory(...$parameters)
 */
class SubscriptionInvoice extends Model implements
    Entity,
    IsRecurringlyBillable,
    HasUrl,
    HasTitle
{
    use HasEntity;
    use HasFactory;
    use RecurringlyBillableTrait;
    use HasUserMorph;

    public const ENTITY_TYPE = 'subscription_invoice';

    protected $table = 'subscription_invoices';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'package_id',
        'user_id',
        'user_type',
        'currency',
        'initial_price',
        'recurring_price',
        'payment_status',
        'renew_type',
        'payment_gateway',
        'is_canceled_by_gateway',
        'created_at',
        'activated_at',
        'expired_at',
        'notified_at',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class, 'package_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class, 'package_id');
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class, 'payment_gateway');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SubscriptionInvoiceTransaction::class, 'invoice_id');
    }

    public function activeTransactions(): HasMany
    {
        return $this->hasMany(SubscriptionInvoiceTransaction::class, 'invoice_id')
            ->where('payment_status', '=', Helper::getCompletedPaymentStatus());
    }

    public function userCanceledReason(): HasOne
    {
        return $this->hasOne(SubscriptionUserCancelReason::class, 'invoice_id')
            ->with(['reason']);
    }

    /**
     * @return SubscriptionInvoiceFactory
     */
    protected static function newFactory()
    {
        return SubscriptionInvoiceFactory::new();
    }

    public function getIsRecurringAttribute(): bool
    {
        return null !== $this->renew_type && null !== $this->recurring_price;
    }

    public function getIsRecurringPaymentAttribute(): bool
    {
        return $this->is_recurring && $this->renew_type == Helper::RENEW_TYPE_AUTO;
    }

    public function getPaymentType(): string
    {
        if ($this->is_recurring_payment) {
            return Payment::PAYMENT_RECURRING;
        }

        return Payment::PAYMENT_ONETIME;
    }

    public function getTrialFrequencyAttribute(): string
    {
        return $this->billing_frequency;
    }

    public function getTrialIntervalAttribute(): int
    {
        return $this->billing_interval;
    }

    public function getTrialAmountAttribute(): float
    {
        if (!$this->is_recurring_payment) {
            return 0;
        }

        return $this->initial_price;
    }

    public function getBillingFrequencyAttribute(): string
    {
        if (!$this->is_recurring_payment) {
            return '';
        }

        return match ($this->package->recurring_period) {
            Helper::RECURRING_PERIOD_MONTHLY, Helper::RECURRING_PERIOD_QUARTERLY, Helper::RECURRING_PERIOD_BIANNUALLY => PaymentSupport::BILLING_MONTHLY,
            Helper::RECURRING_PERIOD_ANNUALLY                                                                         => PaymentSupport::BILLING_ANNUALLY,
        };
    }

    public function getBillingIntervalAttribute(): int
    {
        if (!$this->is_recurring_payment) {
            return 0;
        }

        return match ($this->package->recurring_period) {
            Helper::RECURRING_PERIOD_MONTHLY, Helper::RECURRING_PERIOD_ANNUALLY => 1,
            Helper::RECURRING_PERIOD_QUARTERLY                                  => 3,
            Helper::RECURRING_PERIOD_BIANNUALLY                                 => 6,
        };
    }

    public function getBillingAmountAttribute(): float
    {
        if (!$this->is_recurring_payment) {
            return 0;
        }

        return $this->recurring_price;
    }

    public function getTotalAttribute(): float
    {
        if ($this->is_recurring && $this->renew_type == Helper::RENEW_TYPE_MANUAL && $this->payment_status == Helper::getCompletedPaymentStatus()) {
            return $this->recurring_price;
        }

        return $this->initial_price;
    }

    public function isCompleted(): bool
    {
        return $this->payment_status == Helper::getCompletedPaymentStatus();
    }

    public function isPendingPayment(): bool
    {
        return $this->payment_status == Helper::getPendingPaymentStatus();
    }

    public function isCanceled(): bool
    {
        return $this->payment_status == Helper::getCanceledPaymentStatus();
    }

    public function isPendingAction(): bool
    {
        return $this->payment_status == Helper::getInitPaymentStatus();
    }

    public function isExpired(): bool
    {
        return $this->payment_status == Helper::getExpiredPaymentStatus();
    }

    public function isManualRenew(): bool
    {
        return $this->renew_type == Helper::RENEW_TYPE_MANUAL;
    }

    public function isAutolRenew(): bool
    {
        return $this->renew_type == Helper::RENEW_TYPE_AUTO;
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('subscription/' . $this->entityId());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('subscription/' . $this->entityId());
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('subscription/' . $this->entityId());
    }

    public function toTitle(): string
    {
        $title = '';

        if (null !== $this->package) {
            $title = $this->package->toTitle();
        }

        return __p('subscription::phrase.subscription_for_package_title', [
            'title' => $title,
        ]);
    }

    public function toAdmincpUrl(): string
    {
        return url_utility()->makeApiFullUrl('admincp/subscription/invoice/detail/' . $this->entityId());
    }

    public function toAdmincpLink(): string
    {
        return url_utility()->makeApiUrl('admincp/subscription/invoice/detail/' . $this->entityId());
    }

    public function getExpiredDescriptionAttribute(): ?string
    {
        if (!$this->is_recurring) {
            return __p('subscription::phrase.never_expires');
        }

        if ($this->package->is_deleted) {
            return __p('subscription::phrase.no_longer_available');
        }

        return null;
    }

    public function getPaymentDescriptionAttribute(): string
    {
        return __p('subscription::phrase.purchase_subscription_description', [
            'site_title' => Settings::get('core.general.site_name', 'MetaFox'),
        ]);
    }
}
