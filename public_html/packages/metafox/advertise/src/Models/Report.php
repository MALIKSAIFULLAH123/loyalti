<?php

namespace MetaFox\Advertise\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Advertise\Database\Factories\ReportFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Report.
 *
 * @property int $id
 */
class Report extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'advertise_report';

    protected $table = 'advertise_reports';

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'item_type',
        'total_impression',
        'total_click',
        'date_type',
        'date_value',
    ];

    public $timestamps = false;
}

// end
