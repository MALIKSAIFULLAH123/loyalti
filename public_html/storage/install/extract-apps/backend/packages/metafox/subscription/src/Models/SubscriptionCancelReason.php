<?php

namespace MetaFox\Subscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Subscription\Database\Factories\SubscriptionCancelReasonFactory;
use MetaFox\Subscription\Support\Helper;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class SubscriptionCancelReason.
 *
 * @property        int                             $id
 * @property        string                          $title
 * @property        string                          $title_var
 * @method   static SubscriptionCancelReasonFactory factory(...$parameters)
 */
class SubscriptionCancelReason extends Model implements
    Entity,
    HasTitle,
    HasAmounts
{
    use HasEntity;
    use HasFactory;
    use HasAmountsTrait;
    use HasTranslatableAttributes;

    public const ENTITY_TYPE = 'subscription_cancel_reason';

    protected $table = 'subscription_cancel_reasons';

    /** @var string[] */
    protected $fillable = [
        'title',
        'status',
        'is_default',
        'total_canceled',
        'ordering',
    ];

    protected $translatableAttributes = [
        'title',
    ];

    /**
     * @return SubscriptionCancelReasonFactory
     */
    protected static function newFactory()
    {
        return SubscriptionCancelReasonFactory::new();
    }

    public function toTitle(): string
    {
        if (null === $this->title) {
            return '';
        }

        return Helper::handleTitleForView($this->title);
    }

    public function getTitleAttribute($value): string
    {
        return __p($value);
    }

    public function getTitleVarAttribute(): string
    {
        return Arr::get($this->attributes, 'title');
    }

    public function getIsActiveAttribute(): bool
    {
        return Helper::isActive($this->status);
    }

    public function canceledUserReasons(): HasMany
    {
        return $this->hasMany(SubscriptionUserCancelReason::class, 'reason_id');
    }
}
