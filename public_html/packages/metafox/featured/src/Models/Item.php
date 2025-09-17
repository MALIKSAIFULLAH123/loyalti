<?php

namespace MetaFox\Featured\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Support\Constants;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Featured\Database\Factories\ItemFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Platform\Contracts\User;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class Item
 *
 * @property int $id
 * @property string $status
 * @property int $package_id
 * @property string|null $package_duration_period
 * @property int|null $package_duration_value
 * @property string|null $expired_at
 * @property string|null $notified_at
 * @property string $created_at
 * @property string $updated_at
 * @property Package $package
 * @property bool $is_unpaid
 * @property bool $is_running
 * @property bool $is_ended
 * @property bool $is_cancelled
 * @property string|null $deleted_item_title
 * @property string|null $status_text
 * @property string|null $duration
 * @property Invoice|null $unpaidInvoice
 * @property array|null $payment_information
 * @property bool $is_free
 * @property string|null $item_type_label
 * @property string|null $pricing
 * @method static ItemFactory factory(...$parameters)
 */
class Item extends Model implements Entity, HasUrl
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasItemMorph;

    public const ENTITY_TYPE = 'featured_item';

    protected $table = 'featured_items';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'item_id',
        'item_type',
        'status',
        'package_id',
        'package_duration_period',
        'package_duration_value',
        'deleted_item_title',
        'is_free',
        'expired_at',
        'notified_at',
        'created_at',
        'updated_at',
    ];

    /**
     * @return ItemFactory
     */
    protected static function newFactory()
    {
        return ItemFactory::new();
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function getIsUnpaidAttribute(): bool
    {
        return $this->status === Constants::FEATURED_ITEM_STATUS_UNPAID;
    }

    public function getIsRunningAttribute(): bool
    {
        return $this->status === Constants::FEATURED_ITEM_STATUS_RUNNING;
    }

    public function getIsEndedAttribute(): bool
    {
        return $this->status === Constants::FEATURED_ITEM_STATUS_ENDED;
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === Constants::FEATURED_ITEM_STATUS_CANCELLED;
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('featured?id=' . $this->entityId());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('featured?id=' . $this->entityId());
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('featured/featured_item/' . $this->entityId());
    }

    public function getStatusTextAttribute(): ?string
    {
        $status = $this->status;

        if (!is_string($status)) {
            return null;
        }

        return Feature::getItemStatusText($status);
    }

    public function getDurationAttribute(): ?string
    {
        return Feature::getDurationText($this->package_duration_period, $this->package_duration_value);
    }

    public function unpaidInvoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'featured_id', 'id')
            ->where('featured_invoices.status', '=', Feature::getInitPaymentStatus())
            ->orderByDesc('featured_invoices.id');
    }

    public function getPaymentInformationAttribute(): ?array
    {
        if (!$this->is_unpaid) {
            return null;
        }

        if (!$this->package instanceof Package) {
            return null;
        }

        if (!$this->user instanceof User) {
            return null;
        }

        if (!$this->unpaidInvoice instanceof Invoice) {
            return [
                'price' => $this->package->getPriceForUser($this->user),
                'currency' => app('currency')->getUserCurrencyId($this->user),
            ];
        }

        return [
            'price' => $this->unpaidInvoice->price,
            'currency' => $this->unpaidInvoice->currency,
        ];
    }

    public function getItemTypeLabelAttribute(): ?string
    {
        return Feature::getEntityTypeLabelByEntityType($this->item_type);
    }

    public function getPricingAttribute(): ?string
    {
        $pricing = Constants::PRICING_OPTION_CHARGED;

        if ($this->is_free) {
            $pricing = Constants::PRICING_OPTION_FREE;
        }

        return Feature::getPricingLabel($pricing);
    }
}

// end
