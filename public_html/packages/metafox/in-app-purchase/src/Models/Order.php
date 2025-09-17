<?php

namespace MetaFox\InAppPurchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Payment\Models\Order as PaymentOrder;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Order.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $user_type
 * @property string $platform
 * @property string $product_id
 * @property string $payment_order_id
 * @property string $purchase_token
 * @property string $original_transaction_id
 * @property string $transaction_id
 * @property string $expires_at
 * @property string $created_at
 * @property string $updated_at
 */
class Order extends Model implements Entity
{
    use HasEntity;
    use HasUserMorph;

    public const ENTITY_TYPE = 'iap_order';

    protected $table = 'iap_orders';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'platform',
        'product_id',
        'payment_order_id',
        'purchase_token',
        'original_transaction_id',
        'transaction_id',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function paymentOrder(): BelongsTo
    {
        return $this->belongsTo(PaymentOrder::class);
    }
}
