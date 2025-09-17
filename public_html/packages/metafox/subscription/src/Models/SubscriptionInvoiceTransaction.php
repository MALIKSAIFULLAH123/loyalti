<?php

namespace MetaFox\Subscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Subscription\Database\Factories\SubscriptionInvoiceTransactionFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class SubscriptionInvoiceTransaction.
 *
 * @property int    $id
 * @method   static SubscriptionInvoiceTransactionFactory factory(...$parameters)
 */
class SubscriptionInvoiceTransaction extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'subscription_invoice_transaction';

    protected $table = 'subscription_invoice_transactions';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'invoice_id',
        'payment_status',
        'currency',
        'payment_type',
        'payment_gateway',
        'transaction_id',
        'paid_price',
        'created_at',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionInvoice::class, 'invoice_id');
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class, 'payment_gateway');
    }

    /**
     * @return SubscriptionInvoiceTransactionFactory
     */
    protected static function newFactory()
    {
        return SubscriptionInvoiceTransactionFactory::new();
    }
}
