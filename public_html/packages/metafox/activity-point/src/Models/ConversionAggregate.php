<?php

namespace MetaFox\ActivityPoint\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class ConversionAggregate
 *
 * @property int $id
 */
class ConversionAggregate extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'activitypoint_conversion_aggregate';

    protected $table = 'apt_conversion_aggregate';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'date',
        'total'
    ];

    public $timestamps = false;

    public $dates = ['date'];

    public $casts = [
        'total' => 'integer'
    ];
}

// end
