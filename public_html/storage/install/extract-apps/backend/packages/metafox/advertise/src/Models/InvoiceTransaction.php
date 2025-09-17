<?php

namespace MetaFox\Advertise\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Database\Factories\InvoiceTransactionFactory;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class InvoiceTransaction.
 *
 * @property        int    $id
 * @property        int    $invoice_id
 * @property        string $status
 * @property        string $status_label
 * @property        mixed  $price
 * @property        string $currency_id
 * @property        int    $transaction_id
 * @property        string $created_at
 * @method   static InvoiceTransactionFactory factory(...$parameters)
 */
class InvoiceTransaction extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'advertise_invoice_transaction';

    protected $table = 'advertise_invoice_transactions';

    /** @var string[] */
    protected $fillable = [
        'invoice_id',
        'status',
        'price',
        'currency_id',
        'transaction_id',
    ];

    /**
     * @return InvoiceTransactionFactory
     */
    protected static function newFactory()
    {
        return InvoiceTransactionFactory::new();
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function getStatusLabelAttribute(): string
    {
        $info = Facade::getInvoiceStatusInfo(Arr::get($this->attributes, 'status'));

        if (null === $info) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return Arr::get($info, 'label', MetaFoxConstant::EMPTY_STRING);
    }
}
