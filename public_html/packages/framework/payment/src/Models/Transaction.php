<?php

namespace MetaFox\Payment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use MetaFox\Payment\Database\Factories\TransactionFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Transaction.
 *
 * @property        int                $id
 * @property        int                $gateway_id
 * @property        int                $order_id
 * @property        int                $user_id
 * @property        string             $user_type
 * @property        float              $amount
 * @property        string             $currency
 * @property        string             $status
 * @property        string             $gateway_order_id
 * @property        string             $gateway_transaction_id
 * @property        array              $raw_data
 * @property        string             $created_at
 * @property        string             $updated_at
 * @property        Gateway|null       $gateway
 * @property        string             $status_text
 * @method   static TransactionFactory factory(...$parameters)
 */
class Transaction extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'payment_transaction';

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PENDING   = 'pending';
    public const STATUS_FAILED    = 'failed';

    protected $table = 'payment_transactions';

    /** @var string[] */
    protected $fillable = [
        'gateway_id',
        'order_id',
        'user_id',
        'user_type',
        'amount',
        'currency',
        'status',
        'gateway_order_id',
        'gateway_transaction_id',
        'raw_data',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'raw_data' => 'array',
        'amount'   => 'float',
    ];

    /**
     * @return TransactionFactory
     */
    protected static function newFactory()
    {
        return TransactionFactory::new();
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class, 'gateway_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function isStatusCompleted(): bool
    {
        return $this->status == self::STATUS_COMPLETED;
    }

    public function isStatusFailed(): bool
    {
        return $this->status == self::STATUS_FAILED;
    }

    public function isStatusPending(): bool
    {
        return $this->status == self::STATUS_PENDING;
    }

    public function getStatusTextAttribute(): string
    {
        $options = app('payment.support')->getTransactionStatusOptions();

        $options = array_combine(array_column($options, 'value'), array_column($options, 'label'));

        $label = Arr::get($options, $this->getAttributeFromArray('status'));

        if (null !== $label) {
            return $label;
        }

        return __p('core::phrase.n_a');
    }

    public function toAdminCPLink(): string
    {
        return url_utility()->makeApiUrl('payment/transaction/detail/' . $this->entityId());
    }
}

// end
