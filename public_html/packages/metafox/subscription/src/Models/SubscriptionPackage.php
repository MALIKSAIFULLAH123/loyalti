<?php

namespace MetaFox\Subscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Subscription\Database\Factories\SubscriptionPackageFactory;
use MetaFox\Subscription\Support\Helper;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Support\Facades\User;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class SubscriptionPackage.
 *
 * @property        int                           $id
 * @property        mixed                         $recurring_period
 * @property        string|null                   $image_file_id
 * @property        string                        $price
 * @property        string                        $recurring_price
 * @property        string                        $allowed_renew_type
 * @property        string                        $title
 * @property        string                        $status
 * @property        bool                          $is_popular
 * @property        bool                          $is_on_registration
 * @property        bool                          $is_free
 * @property        string                        $created_at
 * @property        string                        $updated_at
 * @property        string                        $description
 * @property        int                           $total_success
 * @property        int                           $total_expired
 * @property        int                           $total_canceled
 * @property        int                           $upgraded_role_id
 * @property        bool                          $is_deleted
 * @property        bool                          $is_active
 * @property        bool                          $is_recurring
 * @property        SubscriptionDependencyPackage $downgradedPackage
 * @property        SubscriptionDependencyPackage $upgradedPackages
 * @property        array|null                    $renew_methods
 * @method   static SubscriptionPackageFactory    factory(...$parameters)
 */
class SubscriptionPackage extends Model implements
    Entity,
    HasThumbnail,
    HasTitle,
    HasAmounts
{
    use HasEntity;
    use HasFactory;
    use HasNestedAttributes;
    use HasThumbnailTrait;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'subscription_package';

    protected $table = 'subscription_packages';

    /** @var string[] */
    protected $fillable = [
        'title',
        'status',
        'price',
        'recurring_price',
        'recurring_period',
        'upgraded_role_id',
        'image_file_id',
        'is_on_registration',
        'is_popular',
        'is_free',
        'allowed_renew_type',
        'days_notification_before_subscription_expired',
        'background_color_for_comparison',
        'visible_roles',
        'ordering',
        'total_success',
        'total_pending',
        'total_canceled',
        'total_expired',
        'created_at',
        'updated_at',
    ];

    protected $nestedAttributes = [
        'description' => ['text', 'text_parsed'],
    ];

    protected $casts = [
        'is_popular' => 'boolean',
    ];

    public function comparisonData(): HasMany
    {
        return $this->hasMany(SubscriptionComparisonData::class, 'package_id')
            ->join('subscription_comparisons', function (JoinClause $joinClause) {
                $joinClause->on('subscription_comparisons.id', '=', 'subscription_comparisons_data.comparison_id');
            })
            ->select(['subscription_comparisons.title as comparisons_title', 'subscription_comparisons_data.*']);
    }

    public function description(): HasOne
    {
        return $this->hasOne(SubscriptionPackageText::class, 'id');
    }

    public function isPurchased(): HasOne
    {
        $context = User::getGuestUser();

        if (Auth::id() != MetaFoxConstant::GUEST_USER_ID) {
            $context = user();
        }

        return $this->hasOne(SubscriptionInvoice::class, 'package_id')
            ->where([
                'subscription_invoices.payment_status' => Helper::getCompletedPaymentStatus(),
                'subscription_invoices.user_id'        => $context->entityId(),
                'subscription_invoices.user_type'      => $context->entityType(),
            ]);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class, 'package_id');
    }

    public function activeSubscriptions(): HasMany
    {
        $subscriptions = $this->subscriptions();

        return $subscriptions
            ->where('subscription_invoices.payment_status', '=', Helper::getCompletedPaymentStatus());
    }

    public function upgradedPackages(): HasMany
    {
        return $this->hasMany(SubscriptionDependencyPackage::class, 'current_package_id')
            ->where('dependency_type', '=', Helper::DEPENDENCY_UPGRADE);
    }

    public function downgradedPackage(): HasOne
    {
        return $this->hasOne(SubscriptionDependencyPackage::class, 'current_package_id')
            ->where('dependency_type', '=', Helper::DEPENDENCY_DOWNGRADE);
    }

    public function getSizes(): array
    {
        return Helper::getPackageImageSizes();
    }

    public function getThumbnail(): ?string
    {
        return $this->image_file_id;
    }

    public function getPrices(): ?array
    {
        if (null === $this->price) {
            return null;
        }

        $prices = $this->price;

        if (is_string($prices)) {
            $prices = json_decode($prices, true);
        }

        if (!is_array($prices)) {
            return null;
        }

        return $this->roundPriceUp($prices);
    }

    public function getRecurringPrices(): ?array
    {
        if (null === $this->recurring_price) {
            return null;
        }

        $prices = $this->recurring_price;

        if (is_string($prices)) {
            $prices = json_decode($prices, true);
        }

        if (!is_array($prices)) {
            return null;
        }

        return $this->roundPriceUp($prices);
    }

    protected function roundPriceUp(array $prices): array
    {
        foreach ($prices as $key => $price) {
            if (!is_numeric($price)) {
                continue;
            }

            $prices[$key] = round($price, 2);
        }

        return $prices;
    }

    public function getAllowedRenewTypes(): ?array
    {
        if (null === $this->allowed_renew_type) {
            return null;
        }

        $renewTypes = json_decode($this->allowed_renew_type, true);

        if (false === $renewTypes) {
            return null;
        }

        return $renewTypes;
    }

    /**
     * @return SubscriptionPackageFactory
     */
    protected static function newFactory()
    {
        return SubscriptionPackageFactory::new();
    }

    public function toTitle(): string
    {
        if (null === $this->title) {
            return '';
        }

        return Helper::handleTitleForView($this->title);
    }

    public function getIsRecurringAttribute(): bool
    {
        return null !== $this->recurring_period;
    }

    public function getIsActiveAttribute(): bool
    {
        return Helper::isActive($this->status);
    }

    public function getIsDeletedAttribute(): bool
    {
        return Helper::isDeleted($this->status);
    }

    public function toInvoicesLink(string $status, int $packageId): string
    {
        return url_utility()->makeApiFullUrl('admincp/subscription/invoice/browse?payment_status=' . $status . '&package_id=' . $packageId);
    }

    public function successRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'upgraded_role_id', 'id');
    }

    public function getTypeText(): ?string
    {
        if ($this->is_recurring) {
            return Helper::getPeriodLabel($this->recurring_period);
        }

        return __p('subscription::phrase.one_time');
    }

    public function getPriceFormatted(UserModel $context): ?string
    {
        $packagePrices = $this->getPrices();

        if (!is_array($packagePrices)) {
            return null;
        }

        $userCurrency = app('currency')->getUserCurrencyId($context);

        if (!Arr::has($packagePrices, $userCurrency)) {
            return null;
        }

        return app('currency')->getPriceFormatByCurrencyId($userCurrency, Arr::get($packagePrices, $userCurrency));
    }

    public function getRenewMethodsAttribute(): ?array
    {
        $methods = $this->allowed_renew_type;

        if (is_string($methods)) {
            $methods = json_decode($methods, true);
        }

        if (!is_array($methods) || !count($methods)) {
            return null;
        }

        return $methods;
    }
}
