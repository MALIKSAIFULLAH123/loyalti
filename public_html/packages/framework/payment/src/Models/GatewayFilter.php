<?php

namespace MetaFox\Payment\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class GatewayFilter.
 *
 * @property int        $id
 * @property string     $entity_type
 * @property Collection $gateways
 */
class GatewayFilter extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'payment_gateway_filter';

    protected $table = 'payment_gateway_filters';

    /** @var string[] */
    protected $fillable = [
        'entity_type',
        'created_at',
        'updated_at',
    ];

    public function gateways(): BelongsToMany
    {
        return $this->belongsToMany(
            Gateway::class,
            'payment_gateway_filter_data',
            'filter_id',
            'gateway_id',
        )->using(FilterData::class);
    }
}

// end
