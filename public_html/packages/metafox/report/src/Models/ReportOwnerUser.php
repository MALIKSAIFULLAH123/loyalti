<?php

namespace MetaFox\Report\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Report\Database\Factories\ReportOwnerUserFactory;

/**
 * Class ReportOwnerUser.
 *
 * @property int          $id
 * @property int          $reason_id
 * @property int          $report_id
 * @property string       $ip_address
 * @property string       $feedback
 * @property string       $created_at
 * @property string       $updated_at
 * @property ReportReason $reason
 * @property ReportOwner  $report
 * @method   static       ReportOwnerUserFactory factory(...$parameters)
 */
class ReportOwnerUser extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'report_owner_user';

    protected $table = 'report_owner_users';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'reason_id',
        'report_id',
        'ip_address',
        'feedback',
    ];

    /**
     * @return ReportOwnerUserFactory
     */
    protected static function newFactory(): ReportOwnerUserFactory
    {
        return ReportOwnerUserFactory::new();
    }

    /**
     * @return HasOne
     */
    public function reason(): HasOne
    {
        return $this->hasOne(ReportReason::class, 'id', 'reason_id')->withDefault();
    }

    /**
     * @return HasOne
     */
    public function report(): HasOne
    {
        return $this->hasOne(ReportOwner::class, 'id', 'report_id');
    }
}

// end
