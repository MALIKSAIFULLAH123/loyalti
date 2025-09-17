<?php

namespace MetaFox\EMoney\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Facades\UserBalance;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;


/**
 * stub: /packages/models/model.stub
 */

/**
 * Class BalanceAdjustment
 *
 * @property int $id
 * @property string $currency
 * @property float $amount
 * @property string $type
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $type_text
 * @property Transaction|null $transaction
 */
class BalanceAdjustment extends Model implements Entity, HasUrl
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;

    public const ENTITY_TYPE = 'ewallet_balance_adjustment';

    protected $table = 'emoney_balance_adjustments';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'currency',
        'amount',
        'type',
        'created_at',
        'updated_at',
    ];

    public function getTypeTextAttribute(): ?string
    {
        $types = collect(UserBalance::getAdjustmentTypeOptions())
            ->pluck('label', 'value')
            ->toArray();

        return Arr::get($types, $this->type);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'item_id', 'id')
            ->where('emoney_transactions.item_type', '=', $this->entityType());
    }

    public function toUrl(): ?string
    {
        if ($this->transaction instanceof Transaction) {
            return $this->transaction->toUrl();
        }

        return null;
    }

    public function toLink(): ?string
    {
        if ($this->transaction instanceof Transaction) {
            return $this->transaction->toLink();
        }

        return null;
    }

    public function toRouter(): ?string
    {
        if ($this->transaction instanceof Transaction) {
            return $this->transaction->toRouter();
        }

        return null;
    }
}

// end
