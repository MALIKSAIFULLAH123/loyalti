<?php

namespace Foxexpert\Sevent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use MetaFox\Payment\Contracts\IsBillable;
use MetaFox\Payment\Traits\BillableTrait;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Invoice.
 *
 * @property int     $id
 * @property int     $sevent_id
 *  * @property int     $ticket_id
 * @property int     $user_id
 * @property string  $user_type
 * @property float   $price
 * @property string  $currency_id
 * @property string  $paid_at
 * @property string  $status
 * @property string  $created_at
 * @property string  $updated_at
 * @property Sevent $sevent
 *
 * @mixin Builder
 *
 * @method static InvoiceFactory factory(...$parameters)
 */
class Invoice extends Model implements IsBillable, HasUrl
{
    use HasEntity;
    use HasUserMorph;
    use HasFactory;
    use BillableTrait;

    public const ENTITY_TYPE = 'sevent_invoice';

    protected $table = 'sevent_invoices';

    protected $fillable = [
        'sevent_id',
        'ticket_id',
        'user_id',
        'user_type',
        'qty',
        'price',
        'currency_id',
        'payment_gateway',
        'status',
        'paid_at',
    ];

    protected static function newFactory(): InvoiceFactory
    {
        return InvoiceFactory::new();
    }

    public function sevent(): BelongsTo
    {
        return $this->belongsTo(Sevent::class, 'sevent_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Sevent::class, 'sevent_id', 'id');
    }

    public function toTitle(): ?string
    {
        $sevent = $this->sevent;

        if (null === $sevent) {
            return null;
        }

        return $sevent->toTitle();
    }

    public function getTotalAttribute(): float
    {
        return $this->price;
    }

    public function getCurrencyAttribute(): string
    {
        return $this->currency_id;
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('sevent/invoice/' . $this->entityId());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('sevent/invoice/' . $this->entityId());
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('sevent/invoice/' . $this->entityId());
    }

    public function getStatusLabelAttribute(): ?string
    {
        if (null === $this->status) {
            return null;
        }

        return $this->status;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InvoiceTransaction::class, 'invoice_id', 'id')
            ->orderBy('id');
    }

    public function payee(): ?User
    {
        return $this->sevent?->user;
    }
}
