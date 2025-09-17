<?php

namespace MetaFox\EMoney\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use MetaFox\App\Models\Package;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\EMoney\Database\Factories\TransactionFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Transaction.
 *
 * @property        int                $id
 * @property        int                $user_id
 * @property        string             $user_type
 * @property        int                $owner_id
 * @property        string             $owner_type
 * @property        int                $item_id
 * @property        string             $item_type
 * @property        string             $type
 * @property        string             $module_id
 * @property        string             $total_currency
 * @property        float              $total_price
 * @property        string             $commission_currency
 * @property        float              $commission_price
 * @property        string             $actual_currency
 * @property        float              $actual_price
 * @property        string             $balance_currency
 * @property        float              $balance_price
 * @property        float              $current_balance_price
 * @property        float              $exchange_rate
 * @property        int                $exchange_rate_id
 * @property        int                $exchange_rate_log_id
 * @property        string             $status
 * @property        string             $actor_type
 * @property        string             $available_at
 * @property        string             $created_at
 * @property        string             $updated_at
 * @property        bool               $is_pending
 * @property        bool               $is_approved
 * @property        string             $source
 * @property        string             $source_text
 * @property        string             $outgoing_order_id
 * @property        array|null         $extra
 * @property        string             $type_text
 * @method   static TransactionFactory factory(...$parameters)
 */
class Transaction extends Model implements Entity, HasUrl
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;

    public const ENTITY_TYPE = 'ewallet_transaction';

    protected $table = 'emoney_transactions';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'item_id',
        'item_type',
        'type',
        'module_id',
        'total_currency',
        'total_price',
        'commission_currency',
        'commission_price',
        'actual_currency',
        'actual_price',
        'balance_currency',
        'balance_price',
        'current_balance_price',
        'exchange_rate',
        'exchange_rate_id',
        'exchange_rate_log_id',
        'status',
        'actor_type',
        'available_at',
        'created_at',
        'updated_at',
        'source',
        'outgoing_order_id',
        'extra',
    ];

    public $casts = [
        'total_price'           => 'float',
        'commission_price'      => 'float',
        'actual_price'          => 'float',
        'balance_price'         => 'float',
        'current_balance_price' => 'float',
        'extra'                 => 'array',
    ];

    /**
     * @return TransactionFactory
     */
    protected static function newFactory()
    {
        return TransactionFactory::new();
    }

    public function isSystemActor(): bool
    {
        return $this->actor_type === Support::TRANSACTION_ACTOR_TYPE_SYSTEM;
    }

    public function balanceInfo(): array
    {
        return Emoney::getTransactionBalanceInfo($this);
    }

    public function statusInfo(): array
    {
        return Emoney::getTransactionStatusInfo($this->status);
    }

    public function isIncoming(): bool
    {
        return $this->source === Support::TRANSACTION_SOURCE_INCOMING;
    }

    public function getIsPendingAttribute(): bool
    {
        return Arr::get($this->attributes, 'status') == Support::TRANSACTION_STATUS_PENDING;
    }

    public function getIsApprovedAttribute(): bool
    {
        return Arr::get($this->attributes, 'status') == Support::TRANSACTION_STATUS_APPROVED;
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('ewallet/transaction?id=' . $this->entityId());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('ewallet/transaction?id=' . $this->entityId());
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiResourceUrl($this->entityType(), $this->entityId());
    }

    public function getStatusTextAttribute(): string
    {
        $status = $this->getAttributeFromArray('status');

        return match ($status) {
            Support::TRANSACTION_STATUS_APPROVED => __p('ewallet::phrase.processed'),
            Support::TRANSACTION_STATUS_PENDING  => __p('core::phrase.pending'),
            default                              => __p('ewallet::phrase.unknown')
        };
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'module_id', 'alias');
    }

    public function getSourceTextAttribute(): string
    {
        $source = $this->getAttributeFromArray('source');

        if ($source == Support::TRANSACTION_SOURCE_OUTGOING) {
            return __p('ewallet::phrase.outgoing');
        }

        return __p('ewallet::phrase.incoming');
    }

    public function getTypeTextAttribute(): string
    {
        if (!is_array($this->extra) || !is_array($typeDescription = Arr::get($this->extra, 'type_description'))) {
            return match ($this->getAttributeFromArray('type')) {
                Support::INCOMING_TRANSACTION_TYPE_RECEIVED  => __p('ewallet::phrase.bought_your_item'),
                Support::OUTGOING_TRANSACTION_TYPE_PURCHASED => __p('ewallet::phrase.purchased_an_item'),
                Support::OUTGOING_TRANSACTION_TYPE_WITHDRAWN => __p('ewallet::phrase.withdrawn'),
                default                                      => __p('ewallet::phrase.unknown'),
            };
        }

        return __p(Arr::get($typeDescription, 'phrase'), Arr::get($typeDescription, 'params', []));
    }
}
