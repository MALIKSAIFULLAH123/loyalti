<?php

namespace MetaFox\Advertise\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use MetaFox\Advertise\Contracts\AdvertisePaymentInterface;
use MetaFox\Advertise\Database\Factories\AdvertiseFactory;
use MetaFox\Advertise\Notifications\PendingAdvertiseNotification;
use MetaFox\Advertise\Repositories\AdvertiseHideRepositoryInterface;
use MetaFox\Advertise\Repositories\AdvertiseRepositoryInterface;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Advertise\Support\Support;
use MetaFox\Localize\Models\Country as MainModel;
use MetaFox\Localize\Models\Language as MainLanguage;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\UserGender;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Advertise.
 *
 * @property        int              $id
 * @property        bool             $is_pending
 * @property        bool             $is_completed
 * @property        bool             $is_unpaid
 * @property        bool             $is_denied
 * @property        string           $status_text
 * @property        int              $age_from
 * @property        int              $age_to
 * @property        string           $title
 * @property        bool             $is_active
 * @property        mixed            $start_date
 * @property        mixed            $end_date
 * @property        string           $creation_type
 * @property        array            $image_values
 * @property        array            $html_values
 * @property        string           $url
 * @property        string           $advertise_type
 * @property        string           $created_at
 * @property        string           $updated_at
 * @property        Invoice          $latestUnpaidInvoice
 * @property        int              $placement_id
 * @property        Placement        $placement
 * @method   static AdvertiseFactory factory(...$parameters)
 */
class Advertise extends Model implements
    Entity,
    HasTitle,
    HasUrl,
    AdvertisePaymentInterface
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasThumbnailTrait;

    public const ENTITY_TYPE = 'advertise';

    protected $table = 'advertises';

    /** @var string[] */
    protected $fillable = [
        'placement_id',
        'user_id',
        'user_type',
        'title',
        'creation_type',
        'status',
        'is_active',
        'url',
        'start_date',
        'end_date',
        'total_impression',
        'total_click',
        'advertise_type',
        'age_from',
        'age_to',
        'advertise_file_id',
        'image_values',
        'html_values',
    ];

    protected $casts = [
        'image_values'     => 'array',
        'html_values'      => 'array',
        'placement_id'     => 'integer',
        'is_active'        => 'boolean',
        'total_impression' => 'integer',
        'total_click'      => 'integer',
        'age_from'         => 'integer',
        'age_to'           => 'integer',
    ];

    // where to store resources ?
    public array $fileColumns = [
        'advertise_file_id' => 'photo',
    ];

    /**
     * @return AdvertiseFactory
     */
    protected static function newFactory()
    {
        return AdvertiseFactory::new();
    }

    public function toTitle(): string
    {
        return Arr::get($this->attributes, 'title', MetaFoxConstant::EMPTY_STRING);
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

    public function getThumbnail(): ?string
    {
        return Arr::get($this->attributes, 'advertise_file_id');
    }

    public function statistic(): HasOne
    {
        return $this->hasOne(Statistic::class, 'item_id')
            ->where('item_type', '=', $this->entityType());
    }

    public function isPending(): Attribute
    {
        $status = Arr::get($this->attributes, 'status');

        return Attribute::make(
            get: fn () => $status == Support::ADVERTISE_STATUS_PENDING,
            set: fn () => ['status' => Support::ADVERTISE_STATUS_PENDING],
        );
    }

    public function isApproved(): Attribute
    {
        $status = Arr::get($this->attributes, 'status');

        return Attribute::make(
            get: fn () => $status == Support::ADVERTISE_STATUS_APPROVED,
            set: fn () => ['status' => Support::ADVERTISE_STATUS_APPROVED],
        );
    }

    public function isDenied(): Attribute
    {
        $status = Arr::get($this->attributes, 'status');

        return Attribute::make(
            get: fn () => $status == Support::ADVERTISE_STATUS_DENIED,
            set: fn () => ['status' => Support::ADVERTISE_STATUS_DENIED],
        );
    }

    public function isUnpaid(): Attribute
    {
        $status = Arr::get($this->attributes, 'status');

        return Attribute::make(
            get: fn () => $status == Support::ADVERTISE_STATUS_UNPAID,
            set: fn () => ['status' => Support::ADVERTISE_STATUS_UNPAID],
        );
    }

    public function getCreationTypeLabelAttribute(): ?string
    {
        $type = Arr::get($this->attributes, 'creation_type');

        if (null === $type) {
            return null;
        }

        return match ($type) {
            Support::ADVERTISE_IMAGE => __p('advertise::phrase.image'),
            Support::ADVERTISE_HTML  => __p('advertise::phrase.html'),
            default                  => null,
        };
    }

    public function isHidden(User $user): bool
    {
        return resolve(AdvertiseHideRepositoryInterface::class)->isHidden($user, $this);
    }

    public function placement(): BelongsTo
    {
        return $this->belongsTo(Placement::class, 'placement_id')
            ->withTrashed();
    }

    public function unpaidInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'item_id')
            ->where([
                'item_type'      => $this->entityType(),
                'payment_status' => Facade::getPendingActionStatus(),
            ]);
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('advertise/' . $this->entityId());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('advertise/' . $this->entityId());
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('advertise/' . $this->entityId());
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

    public function isPriceChanged(Invoice $invoice): bool
    {
        if (null === $this->latestUnpaidInvoice) {
            return true;
        }

        if ($invoice->entityId() != $this->latestUnpaidInvoice->entityId()) {
            return true;
        }

        return Facade::isAdvertiseChangePrice($this);
    }

    public function toPayment(User $user): array
    {
        if ($this->latestUnpaidInvoice instanceof Invoice) {
            return $this->toPaymentByUnpaidInvoice();
        }

        $currencyId = app('currency')->getUserCurrencyId($user);

        $placementPrice = Facade::getPlacementPriceByCurrencyId(Arr::get($this->attributes, 'placement_id'), $currencyId);

        if (!is_numeric($placementPrice)) {
            return [];
        }

        $price = Facade::calculateAdvertisePrice($this, $placementPrice);

        if (!is_numeric($price)) {
            return [];
        }

        return [
            'currency_id' => $currencyId,
            'price'       => $price,
        ];
    }

    /**
     * @return array
     */
    protected function toPaymentByUnpaidInvoice(): array
    {
        $currencyId = $this->latestUnpaidInvoice->currency_id;
        $price      = $this->latestUnpaidInvoice->price;
        if ($this->isPriceChanged($this->latestUnpaidInvoice)) {
            $placementPrice = Facade::getPlacementPriceByCurrencyId($this->placement_id, $currencyId);
            $price          = Facade::calculateAdvertisePrice($this, $placementPrice);
        }

        return [
            'currency_id' => $currencyId,
            'price'       => $price,
        ];
    }

    public function toCompletedPayment(Invoice $invoice): bool
    {
        return resolve(AdvertiseRepositoryInterface::class)->updateSuccessPayment($this, $invoice);
    }

    public function getChangePriceMessage(float $price, string $currencyId): string
    {
        return __p('advertise::phrase.change_invoice_description', [
            'price' => app('currency')->getPriceFormatByCurrencyId($currencyId, $price),
        ]);
    }

    public function getFreePriceMessage(): string
    {
        return __p('advertise::phrase.free_advertise_description');
    }

    public function isFree(User $user): bool
    {
        if (null === $this->placement) {
            return false;
        }

        if (null === $this->latestUnpaidInvoice) {
            $price = Facade::getPlacementPriceByCurrencyId($this->placement->entityId(), app('currency')->getUserCurrencyId($user));

            if ($price == 0) {
                return true;
            }

            return false;
        }

        return $this->latestUnpaidInvoice->price == 0;
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Country::class, 'item_id')
            ->where('item_type', '=', self::ENTITY_TYPE);
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(MainModel::class, 'advertise_countries', 'item_id', 'country_code', 'id', 'country_iso')
            ->wherePivot('item_type', '=', self::ENTITY_TYPE)
            ->orderBy('core_countries.name');
    }

    public function getIsCompletedAttribute(): bool
    {
        return Arr::get($this->attributes, 'status') == Support::ADVERTISE_STATUS_COMPLETED;
    }

    public function getIsApprovedAttribute(): bool
    {
        return Arr::get($this->attributes, 'status') == Support::ADVERTISE_STATUS_APPROVED;
    }

    public function getIsPendingAttribute(): bool
    {
        return Arr::get($this->attributes, 'status') == Support::ADVERTISE_STATUS_PENDING;
    }

    public function getSizes(): array
    {
        return ['150', '500', '1024'];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'item_id', 'id')
            ->where('advertise_invoices.item_type', '=', self::ENTITY_TYPE);
    }

    public function toPendingItem(Invoice $invoice): void
    {
        if (null === $this->user) {
            return;
        }

        $notification = new PendingAdvertiseNotification($this);

        $params = [$this->user, $notification];

        Notification::send(...$params);
    }

    public function getStatusInformationAttribute(): ?array
    {
        return Facade::getAdvertiseStatusInfo(Arr::get($this->attributes, 'status'));
    }

    public function toPaymentDescription(): string
    {
        return __p('advertise::phrase.purchase_advertise_description', [
            'title'      => $this->title,
            'site_title' => Settings::get('core.general.site_name', 'MetaFox'),
        ]);
    }

    public function getPaymentOrderTitleAttribute(): ?string
    {
        return __p('advertise::phrase.ad_title', [
            'title' => $this->toTitle(),
        ]);
    }
}
