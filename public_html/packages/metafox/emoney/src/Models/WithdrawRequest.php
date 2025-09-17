<?php

namespace MetaFox\EMoney\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Database\Factories\WithdrawRequestFactory;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Notifications\PendingWithdrawRequestNotification;
use MetaFox\EMoney\Support\Support;
use MetaFox\Payment\Contracts\IsBillable;
use MetaFox\Payment\Traits\BillableTrait;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class WithdrawRequest.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $user_type
 * @property string $currency
 * @property float  $amount
 * @property float  $total
 * @property float  $fee
 * @property string $withdraw_service
 * @property string $status
 * @property string $processed_at
 * @property string $transaction_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @method static WithdrawRequestFactory factory(...$parameters)
 */
class WithdrawRequest extends Model implements Entity, HasUrl, IsBillable, IsNotifyInterface, HasTitle
{
    use HasEntity;
    use HasFactory;
    use BillableTrait;
    use HasUserMorph;

    public const ENTITY_TYPE = 'ewallet_withdraw_request';

    protected $table = 'emoney_withdraw_requests';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'currency',
        'amount',
        'total',
        'fee',
        'withdraw_service',
        'processed_at',
        'transaction_id',
        'status',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'amount' => 'float',
        'total'  => 'float',
        'fee'    => 'float',
    ];

    /**
     * @return WithdrawRequestFactory
     */
    protected static function newFactory()
    {
        return WithdrawRequestFactory::new();
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('ewallet/request?id=' . $this->entityId());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('ewallet/request?id=' . $this->entityId());
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiResourceUrl($this->entityType(), $this->entityId());
    }

    public function getTotal(): float
    {
        return Arr::get($this->attributes, 'amount');
    }

    public function payee(): ?User
    {
        return $this->user;
    }

    public function product(): mixed
    {
        return $this;
    }

    public function toNotification(): ?array
    {
        $status = $this->getAttributeFromArray('status');

        if ($status != Support::WITHDRAW_STATUS_PENDING) {
            return null;
        }

        $superAdmins = Emoney::getNotifiables();

        if ($superAdmins->isEmpty()) {
            return null;
        }

        $notifiables = [];
        foreach ($superAdmins as $superAdmin) {
            if (!$superAdmin instanceof IsNotifiable) {
                continue;
            }

            if ($superAdmin->entityId() == $this->userId()) {
                continue;
            }

            $notifiables[] = $superAdmin;
        }

        if (empty($notifiables)) {
            return null;
        }

        return [$notifiables, new PendingWithdrawRequestNotification($this)];
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->getAttributeFromArray('status') == Support::WITHDRAW_STATUS_PENDING;
    }

    public function getIsDeniedAttribute(): bool
    {
        return $this->getAttributeFromArray('status') == Support::WITHDRAW_STATUS_DENIED;
    }

    public function getIsProcessingAttribute(): bool
    {
        return $this->getAttributeFromArray('status') == Support::WITHDRAW_STATUS_PROCESSING;
    }

    public function getIsProcessedAttribute(): bool
    {
        return $this->getAttributeFromArray('status') == Support::WITHDRAW_STATUS_PROCESSED;
    }

    public function withdrawMethod(): BelongsTo
    {
        return $this->belongsTo(WithdrawMethod::class, 'withdraw_service', 'service');
    }

    public function getStatusTextAttribute(): string
    {
        $status = $this->getAttributeFromArray('status');

        $options = Emoney::getRequestStatusOptions();

        $options = array_combine(array_column($options, 'value'), array_column($options, 'label'));

        return Arr::get($options, $status, __p('ewallet::phrase.unknown'));
    }

    public function getTotalTextAttribute(): ?string
    {
        $amount = $this->getAttributeFromArray('total');

        $currency = $this->getAttributeFromArray('currency');

        return app('currency')->getPriceFormatByCurrencyId($currency, $amount);
    }

    public function getFeeTextAttribute(): ?string
    {
        $amount = $this->getAttributeFromArray('fee');

        $currency = $this->getAttributeFromArray('currency');

        return app('currency')->getPriceFormatByCurrencyId($currency, $amount);
    }

    public function getAmountTextAttribute(): ?string
    {
        $amount = $this->getAttributeFromArray('amount');

        $currency = $this->getAttributeFromArray('currency');

        return app('currency')->getPriceFormatByCurrencyId($currency, $amount);
    }

    public function reason(): HasOne
    {
        return $this->hasOne(WithdrawRequestReason::class, 'request_id');
    }

    public function toTitle(): string
    {
        $user = $this->user;

        if (!$user instanceof User) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return __p('ewallet::admin.withdrawal_request_from_user', [
            'full_name' => $user->toTitle(),
        ]);
    }

    public function toAdminCPUrl(): string
    {
        return url_utility()->makeApiFullUrl('admincp/ewallet/request/browse?id=' . $this->entityId());
    }
}
