<?php

namespace MetaFox\Report\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Report\Database\Factories\ReportOwnerFactory;

/**
 * Class ReportOwner.
 *
 * @property int             $id
 * @property int             $total_report
 * @property string          $created_at
 * @property string          $updated_at
 * @property ReportOwnerUser $userReports
 * @method static ReportOwnerFactory factory(...$parameters)
 */
class ReportOwner extends Model implements Entity, HasAmounts
{
    use HasEntity;
    use HasFactory;
    use HasOwnerMorph;
    use HasItemMorph;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'report_owner';

    protected $table = 'report_owners';

    /** @var string[] */
    protected $fillable = [
        'owner_id',
        'owner_type',
        'item_id',
        'item_type',
        'total_report',
    ];

    /**
     * @return ReportOwnerFactory
     */
    protected static function newFactory(): ReportOwnerFactory
    {
        return ReportOwnerFactory::new();
    }

    public function userReports(): HasMany
    {
        return $this->hasMany(ReportOwnerUser::class, 'report_id', 'id');
    }
}

// end
