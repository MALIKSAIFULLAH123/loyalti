<?php

namespace MetaFox\Featured\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Featured\Database\Factories\TransactionFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class Transaction
 *
 * @property int $id
 * @property int $invoice_id
 * @property string $status
 * @property int $payment_gateway
 * @property float $price
 * @property string $currency
 * @property string|null $transaction_id
 * @property string|null $deleted_item_title
 * @property string $created_at
 * @property string $updated_at
 * @property Gateway|null $paymentGateway
 * @property string|null $status_text
 * @property string|null $price_formatted
 * @property string|null $item_type_label
 * @method static TransactionFactory factory(...$parameters)
 */
class Transaction extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasItemMorph;


    public const ENTITY_TYPE = 'featured_transaction';

    protected $table = 'featured_transactions';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'item_id',
        'item_type',
        'invoice_id',
        'status',
        'payment_gateway',
        'price',
        'currency',
        'transaction_id',
        'deleted_item_title',
        'created_at',
        'updated_at',
    ];

    /**
     * @return TransactionFactory
     */
    protected static function newFactory()
    {
        return TransactionFactory::new();
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class, 'payment_gateway', 'id');
    }

    public function getStatusTextAttribute(): ?string
    {
        $status = $this->status;

        if (!is_string($status)) {
            return null;
        }

        return Feature::getInvoiceStatusText($status);
    }

    public function getPriceFormattedAttribute(): ?string
    {
        $price = $this->price;

        $currency = $this->currency;

        if (!is_numeric($price) || !is_string($currency)) {
            return null;
        }

        return Feature::getPriceFormatted($price, $currency);
    }

    public function getItemTypeLabelAttribute(): ?string
    {
        return Feature::getEntityTypeLabelByEntityType($this->item_type);
    }
}

// end
