<?php

namespace MetaFox\ActivityPoint\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\ActivityPoint\Database\Factories\PackagePurchaseFactory;
use MetaFox\Localize\Models\Currency;
use MetaFox\Payment\Contracts\IsBillable;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Traits\BillableTrait;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class PackagePurchase.
 * @mixin Builder
 *
 * @property        int          $id
 * @property        PointPackage $package
 * @property        int          $status
 * @property        float        $price
 * @property        Currency     $currency
 * @property        int          $gateway_id
 * @property        int          $points
 * @property        string       $transaction_id
 * @property        string       $created_at
 * @property        string       $updated_at
 * @property        string       $payment_description
 * @property        Gateway|null $gateway
 * @method   static PackagePurchaseFactory factory(...$parameters)
 */
class PackagePurchase extends Model implements Entity, HasUrl, HasTitle, IsBillable
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use BillableTrait;

    public const ENTITY_TYPE = 'activitypoint_package_purchase';

    public const STATUS_INIT    = 1;
    public const STATUS_SUCCESS = 2;
    public const STATUS_FAILED  = 3;

    protected $table = 'apt_package_purchases';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'package_id',
        'transaction_id',
        'status',
        'price',
        'currency_id',
        'gateway_id',
        'points',
        'created_at',
        'updated_at',
    ];

    protected static function newFactory(): PackagePurchaseFactory
    {
        return PackagePurchaseFactory::new();
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(PointPackage::class, 'package_id', 'id');
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class, 'gateway_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PointPackage::class, 'package_id', 'id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'code');
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('activitypoint/package-transaction?id=' . $this->entityId());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('activitypoint/package-transaction?id=' . $this->entityId());
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiResourceUrl($this->entityType(), $this->entityId());
    }

    public function toAdminCPUrl(): string
    {
        return url_utility()->makeApiFullUrl('admincp/activitypoint/package-transaction/browse?id=' . $this->entityId());
    }

    public function toTitle(): string
    {
        return $this->package?->title ?? __p('activitypoint::phrase.deleted_package');
    }

    public function getTotal(): float
    {
        return $this->price;
    }

    public function getCurrency(): string
    {
        return $this->currency->code;
    }

    public function getPaymentDescriptionAttribute(): string
    {
        return __p('activitypoint::phrase.purchase_package_description', [
            'site_title' => Settings::get('core.general.site_name', 'MetaFox'),
        ]);
    }
}

// end
