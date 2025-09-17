<?php

namespace MetaFox\Payment\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class CategoryData.
 *
 * @property int $id
 * @property int $gateway_id
 * @property int $filter_id
 *
 * @mixin Builder
 */
class FilterData extends Pivot
{
    public $timestamps = false;

    protected $table = 'payment_gateway_filter_data';

    protected $fillable = [
        'gateway_id',
        'filter_id',
    ];
}

// end
