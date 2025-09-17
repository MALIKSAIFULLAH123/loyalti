<?php

namespace MetaFox\EMoney\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class ConversionRate.
 * @property int    $id
 * @property string $base
 * @property string $target
 * @property string $type
 * @property float  $exchange_rate
 * @property int    $log_id
 * @property string $created_at
 * @property string $updated_at
 * @property bool   $is_synchronized
 */
class ConversionRate extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'ewallet_conversion_rate';

    protected $table = 'emoney_currency_conversion_rates';

    /** @var string[] */
    protected $fillable = [
        'base',
        'target',
        'type',
        'exchange_rate',
        'log_id',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'exchange_rate' => 'float',
    ];

    public function log(): BelongsTo
    {
        return $this->belongsTo(CurrencyConversionRateLog::class, 'log_id', 'id');
    }

    public function getIsSynchronizedAttribute(): bool
    {
        return Arr::get($this->attributes, 'type') == Support::TARGET_EXCHANGE_RATE_TYPE_AUTO;
    }
}
