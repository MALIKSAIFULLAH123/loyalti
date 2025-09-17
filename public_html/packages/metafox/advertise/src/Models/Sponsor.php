<?php

namespace MetaFox\Advertise\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use MetaFox\Advertise\Contracts\AdvertisePaymentInterface;
use MetaFox\Advertise\Notifications\PendingSponsorNotification;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Advertise\Support\Support;
use MetaFox\Localize\Models\Language as MainLanguage;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Advertise\Database\Factories\SponsorFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\UserGender;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Sponsor.
 *
 * @property        int            $id
 * @property        string         $sponsor_type
 * @property        string         $sponsor_type_text
 * @property        bool           $is_pending
 * @property        bool           $is_denied
 * @property        bool           $is_approved
 * @property        bool           $is_unpaid
 * @property        Invoice        $latestUnpaidInvoice
 * @property        bool           $is_ended
 * @property        Invoice        $paidInvoice
 * @property        string         $start_date
 * @property        string|null    $end_date
 * @method   static SponsorFactory factory(...$parameters)
 */
class Sponsor extends Model implements
    Entity,
    AdvertisePaymentInterface,
    HasTitle,
    HasUrl
{
    use HasEntity;
    use HasFactory;
    use HasItemMorph;
    use HasUserMorph;

    public const ENTITY_TYPE = 'advertise_sponsor';

    protected $table = 'advertise_sponsors';

    public $timestamps = true;

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'item_type',
        'user_id',
        'user_type',
        'title',
        'status',
        'start_date',
        'end_date',
        'total_impression',
        'total_click',
        'sponsor_type',
        'is_active',
        'age_from',
        'age_to',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return SponsorFactory
     */
    protected static function newFactory()
    {
        return SponsorFactory::new();
    }

    public function latestUnpaidInvoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'item_id')
            ->where([
                'item_type'      => $this->entityType(),
                'payment_status' => Facade::getPendingActionStatus(),
            ])
            ->orderByDesc('id');
    }

    /**
     * @param  Invoice $invoice
     * @return bool
     */
    public function isPriceChanged(Invoice $invoice): bool
    {
        if (null === $this->latestUnpaidInvoice) {
            return true;
        }

        if ($invoice->entityId() != $this->latestUnpaidInvoice->entityId()) {
            return true;
        }

        return Facade::isSponsorChangePrice($this);
    }

    /**
     * @param  User $user
     * @return bool
     */
    public function isFree(User $user): bool
    {
        $price = Facade::getCurrentSponsorPrice($this);

        if (null === $price) {
            return false;
        }

        return 0 == $price;
    }

    /**
     * @param  User  $user
     * @return array
     */
    public function toPayment(User $user): array
    {
        if ($this->latestUnpaidInvoice instanceof Invoice) {
            return $this->toPaymentByUnpaidInvoice($user);
        }

        $price = Facade::getCurrentSponsorPrice($this);

        $currencyId = app('currency')->getUserCurrencyId($this->user);

        if (!is_numeric($price)) {
            return [];
        }

        $price = Facade::calculateSponsorPrice($this, $price);

        return [
            'currency_id' => $currencyId,
            'price'       => $price,
        ];
    }

    /**
     * @param  User  $user
     * @return array
     */
    protected function toPaymentByUnpaidInvoice(User $user): array
    {
        $currencyId = $this->latestUnpaidInvoice->currency_id;

        $price = $this->latestUnpaidInvoice->price;

        if ($this->isPriceChanged($this->latestUnpaidInvoice)) {
            $price = resolve(SponsorSettingServiceInterface::class)->getPriceForPayment($user, $this->item, $currencyId);

            $price = Facade::calculateSponsorPrice($this, $price);
        }

        return [
            'currency_id' => $currencyId,
            'price'       => $price,
        ];
    }

    /**
     * @param  Invoice $invoice
     * @return bool
     */
    public function toCompletedPayment(Invoice $invoice): bool
    {
        return resolve(SponsorRepositoryInterface::class)->updateSuccessPayment($this, $invoice);
    }

    /**
     * @param  float  $price
     * @param  string $currencyId
     * @return string
     */
    public function getChangePriceMessage(float $price, string $currencyId): string
    {
        return __p('advertise::phrase.sponsor_change_invoice_description', [
            'price' => app('currency')->getPriceFormatByCurrencyId($currencyId, $price),
        ]);
    }

    /**
     * @return string
     */
    public function getFreePriceMessage(): string
    {
        return __p('advertise::phrase.free_sponsor_description');
    }

    public function unpaidInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'item_id')
            ->where([
                'item_type'      => $this->entityType(),
                'payment_status' => Facade::getPendingActionStatus(),
            ]);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'item_id', 'id')
            ->where('advertise_invoices.item_type', '=', self::ENTITY_TYPE);
    }

    /**
     * @return string
     */
    public function toTitle(): string
    {
        return Arr::get($this->attributes, 'title', MetaFoxConstant::EMPTY_STRING);
    }

    public function genders(): BelongsToMany
    {
        return $this->belongsToMany(
            UserGender::class,
            'advertise_genders',
            'item_id',
            'gender_id'
        )->using(Gender::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(
            MainLanguage::class,
            'advertise_languages',
            'item_id',
            'language_code',
            'id',
            'language_code'
        )->using(Language::class);
    }

    public function statistic(): HasOne
    {
        return $this->hasOne(Statistic::class, 'item_id')
            ->where('item_type', '=', $this->entityType());
    }

    /**
     * @return string|null
     */
    public function toLink(): ?string
    {
        if ($this->item instanceof Content) {
            return $this->item->toLink();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function toUrl(): ?string
    {
        if ($this->item instanceof Content) {
            return $this->item->toUrl();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function toRouter(): ?string
    {
        if ($this->item instanceof Content) {
            return $this->item->toRouter();
        }

        return null;
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Country::class, 'item_id')
            ->where('item_type', '=', self::ENTITY_TYPE);
    }

    public function getStatusTextAttribute(): string
    {
        $status = Arr::get($this->attributes, 'status');

        switch ($status) {
            case Support::ADVERTISE_STATUS_APPROVED:
                $startDate = Arr::get($this->attributes, 'start_date');

                if (null === $startDate) {
                    $text = __p('advertise::phrase.status.approved');
                    break;
                }

                $startDate = Carbon::parse($startDate);

                $now = Carbon::now();

                if ($startDate->greaterThan($now)) {
                    $text = __p('advertise::phrase.status.upcoming');
                    break;
                }

                $endDate = Arr::get($this->attributes, 'end_date');

                if (null === $endDate) {
                    $text = __p('advertise::phrase.status.running');
                    break;
                }

                if (Carbon::parse($endDate)->greaterThan($now)) {
                    $text = __p('advertise::phrase.status.running');
                    break;
                }

                $text = __p('advertise::phrase.status.ended');
                break;
            case Support::ADVERTISE_STATUS_ENDED:
                $text = __p('advertise::phrase.status.ended');
                break;
            case Support::ADVERTISE_STATUS_PENDING:
                $text = __p('advertise::phrase.status.pending');
                break;
            case Support::ADVERTISE_STATUS_DENIED:
                $text = __p('advertise::phrase.status.denied');
                break;
            case Support::ADVERTISE_STATUS_COMPLETED:
                $text = __p('advertise::phrase.status.completed');
                break;
            default:
                $text = __p('advertise::phrase.status.unpaid');
                break;
        }

        return $text;
    }

    public function getSponsorTypeTextAttribute(): string
    {
        return match ($this->sponsor_type) {
            Support::SPONSOR_TYPE_FEED => __p('advertise::phrase.feed'),
            default                    => __p('advertise::phrase.item'),
        };
    }

    public function getIsApprovedAttribute(): bool
    {
        return Arr::get($this->attributes, 'status') == Support::ADVERTISE_STATUS_APPROVED;
    }

    public function getIsPendingAttribute(): bool
    {
        return Arr::get($this->attributes, 'status') == Support::ADVERTISE_STATUS_PENDING;
    }

    public function getIsUnpaidAttribute(): bool
    {
        return Arr::get($this->attributes, 'status') == Support::ADVERTISE_STATUS_UNPAID;
    }

    public function getIsDeniedAttribute(): bool
    {
        return Arr::get($this->attributes, 'status') == Support::ADVERTISE_STATUS_DENIED;
    }

    public function toPendingItem(Invoice $invoice): void
    {
        if (null === $this->user) {
            return;
        }

        $notification = new PendingSponsorNotification($this);

        $params = [$this->user, $notification];

        Notification::send(...$params);
    }

    public function getIsEndedAttribute(): bool
    {
        $status = Arr::get($this->attributes, 'status');

        if ($status == Support::ADVERTISE_STATUS_ENDED) {
            return true;
        }

        if ($status != Support::ADVERTISE_STATUS_APPROVED) {
            return false;
        }

        $endDate = Arr::get($this->attributes, 'end_date');

        if (null === $endDate) {
            return false;
        }

        $now = Carbon::now();

        $endDate = Carbon::parse($endDate);

        return $endDate->lessThanOrEqualTo($now);
    }

    public function toPaymentReturnUrl(): string
    {
        return url_utility()->makeApiFullUrl('advertise/sponsor');
    }

    public function toPaymentDescription(): string
    {
        $item = $this->item;

        if (null === $item) {
            return __p('advertise::phrase.purchase_sponsorship_description', [
                'site_title' => Settings::get('core.general.site_name', 'MetaFox'),
            ]);
        }

        $itemType = __p_type_key($item->entityType());

        $itemType = str_replace('_', ' ', $itemType);

        $title = null;

        if ($item instanceof HasTitle) {
            $title = $item->toTitle();
        }

        return __p('advertise::phrase.purchase_sponsorship_with_item_description', [
            'site_title' => Settings::get('core.general.site_name', 'MetaFox'),
            'item_type'  => $itemType,
            'title'      => $title,
        ]);
    }

    public function getPaymentOrderTitleAttribute(): ?string
    {
        return __p('advertise::phrase.sponsorship_title', [
            'title' => $this->toTitle(),
        ]);
    }

    public function paidInvoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'item_id', 'id')
            ->where('advertise_invoices.item_type', '=', self::ENTITY_TYPE)
            ->where('advertise_invoices.payment_status', '=', \MetaFox\Advertise\Support\Facades\Support::getCompletedPaymentStatus());
    }
}
