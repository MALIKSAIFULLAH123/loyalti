<?php

namespace MetaFox\EMoney\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class CurrencyConversionRate.
 *
 * @property int    $id
 * @property string $service
 * @property string $from
 * @property string $to
 * @property float  $exchange_rate
 * @property string $payload
 * @property string $response
 * @property string $created_at
 * @property string $updated_at
 */
class CurrencyConversionRateLog extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'ewallet_currency_conversion_rate_log';

    protected $table = 'emoney_currency_conversion_rate_logs';

    /** @var string[] */
    protected $fillable = [
        'service',
        'from',
        'to',
        'exchange_rate',
        'payload',
        'response',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'exchange_rate' => 'float',
    ];

    public function converter(): BelongsTo
    {
        return $this->belongsTo(CurrencyConverter::class, 'service', 'service');
    }
}
