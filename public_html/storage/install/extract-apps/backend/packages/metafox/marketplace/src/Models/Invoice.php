<?php

namespace MetaFox\Marketplace\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use MetaFox\Marketplace\Database\Factories\InvoiceFactory;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;
use MetaFox\Payment\Contracts\IsBillable;
use MetaFox\Payment\Traits\BillableTrait;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Invoice.
 *
 * @property int     $id
 * @property int     $listing_id
 * @property int     $user_id
 * @property string  $user_type
 * @property float   $price
 * @property string  $currency_id
 * @property string  $paid_at
 * @property string  $status
 * @property string  $created_at
 * @property string  $updated_at
 * @property Listing $listing
 * @property float|null $commission_percentage
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

    public const ENTITY_TYPE = 'marketplace_invoice';

    protected $table = 'marketplace_invoices';

    protected $fillable = [
        'listing_id',
        'user_id',
        'user_type',
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

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'id');
    }

    public function toTitle(): ?string
    {
        $listing = $this->listing;

        if (null === $listing) {
            return null;
        }

        return $listing->toTitle();
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
        return url_utility()->makeApiUrl('marketplace/invoice/' . $this->entityId());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('marketplace/invoice/' . $this->entityId());
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('marketplace/invoice/' . $this->entityId());
    }

    public function getStatusLabelAttribute(): ?string
    {
        if (null === $this->status) {
            return null;
        }

        return ListingFacade::getStatusLabel($this->status);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InvoiceTransaction::class, 'invoice_id', 'id')
            ->orderBy('id');
    }

    public function payee(): ?User
    {
        return $this->listing?->user;
    }

    public function toAdmincpUrl(): string
    {
        return url_utility()->makeApiFullUrl('admincp/marketplace/invoice/detail/' . $this->entityId());
    }

    public function getCommissionPercentageAttribute(): ?float
    {
        $value = (float) Settings::get('marketplace.commission_fee');

        if ($value <= 0) {
            return null;
        }

        return $value;
    }
}
