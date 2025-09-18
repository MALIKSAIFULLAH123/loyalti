<?php

namespace MetaFox\Marketplace\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class InvoiceTransaction.
 *
 * @property int    $id
 * @property int    $invoice_id
 * @property string $status
 * @property float  $price
 * @property string $currency_id
 * @property string $transaction_id
 * @property string $created_at
 * @property string $updated_at
 */
class InvoiceTransaction extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'marketplace_invoice_transaction';

    protected $table = 'marketplace_invoice_transactions';

    /** @var string[] */
    protected $fillable = [
        'invoice_id',
        'status',
        'price',
        'currency_id',
        'transaction_id',
        'payment_gateway',
    ];

    public function getStatusLabelAttribute(): ?string
    {
        if (null === $this->status) {
            return null;
        }

        return ListingFacade::getStatusLabel($this->status);
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class, 'payment_gateway');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}

// end
