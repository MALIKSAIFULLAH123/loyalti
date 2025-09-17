<?php

namespace MetaFox\Featured\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Payment\Contracts\IsBillable;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Traits\BillableTrait;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Featured\Database\Factories\InvoiceFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class Invoice
 *
 * @property int $id
 * @property int $featured_id
 * @property int $package_id
 * @property string $status
 * @property int $payment_gateway
 * @property float $price
 * @property string $currency
 * @property string|null $deleted_item_title
 * @property string $created_at
 * @property string $updated_at
 * @property Package|null $package
 * @property bool $is_free
 * @property bool $is_completed
 * @property Item $featuredItem
 * @property string|null $status_text
 * @property string|null $price_formatted
 * @property Transaction|null $paidTransaction
 * @property Gateway|null $paymentGateway
 * @property array|null $status_information
 * @property string|null $item_type_label
 * @method static InvoiceFactory factory(...$parameters)
 */
class Invoice extends Model implements Entity, IsBillable, HasUrl, HasTitle
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasItemMorph;
    use BillableTrait;

    public const ENTITY_TYPE = 'featured_invoice';

    protected $table = 'featured_invoices';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'item_id',
        'item_type',
        'package_id',
        'featured_id',
        'status',
        'payment_gateway',
        'price',
        'currency',
        'deleted_item_title',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    /**
     * @return InvoiceFactory
     */
    protected static function newFactory()
    {
        return InvoiceFactory::new();
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function featuredItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'featured_id', 'id');
    }

    public function getIsFreeAttribute(): bool
    {
        $price = $this->price;

        if (!is_numeric($price)) {
            return false;
        }

        if ($price != 0) {
            return false;
        }

        return true;
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === Feature::getCompletedPaymentStatus();
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('featured/invoice?id=' . $this->entityId());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('featured/invoice?id=' . $this->entityId());
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('featured/featured_invoice/' . $this->entityId());
    }

    public function toAdminCPUrl(): string
    {
        return url_utility()->makeApiFullUrl('admincp/featured/invoice/browse?id=' . $this->entityId());
    }

    public function getStatusInformationAttribute(): ?array
    {
        $status = $this->status;

        if (!is_string($status)) {
            return null;
        }

        return Feature::getInvoiceStatusInfo($status);
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

    public function paidTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'invoice_id', 'id')
            ->where([
                'status' => Feature::getCompletedPaymentStatus(),
            ]);
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class, 'payment_gateway', 'id');
    }

    public function toTitle(): string
    {
        if ($this->item instanceof HasTitle) {
            return Feature::getItemTitle($this->item);
        }

        return MetaFoxConstant::EMPTY_STRING;
    }

    public function getTotalAttribute(): float
    {
        return Arr::get($this->attributes, 'price', 0);
    }

    public function getCurrencyAttribute(): string
    {
        return Arr::get($this->attributes, 'currency', MetaFoxConstant::EMPTY_STRING);
    }

    public function getItemTypeLabelAttribute(): ?string
    {
        return Feature::getEntityTypeLabelByEntityType($this->item_type);
    }
}

// end
