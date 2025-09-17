<?php

namespace MetaFox\Report\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Report\Database\Factories\ReportItemFactory;

/**
 * Class ReportItem.
 *
 * @property int               $id
 * @property int               $reason_id
 * @property string            $ip_address
 * @property string            $feedback
 * @property string            $created_at
 * @property string            $updated_at
 * @property ReportReason|null $reason
 */
class ReportItem extends Model
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasItemMorph;

    public const ENTITY_TYPE = 'report_item';

    protected $table = 'report_items';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'item_id',
        'item_type',
        'reason_id',
        'ip_address',
        'feedback',
    ];

    /**
     * @return HasOne
     */
    public function reason(): HasOne
    {
        return $this->hasOne(ReportReason::class, 'id', 'reason_id');
    }

    /**
     * @return ReportItemFactory
     */
    protected static function newFactory(): ReportItemFactory
    {
        return ReportItemFactory::new();
    }
}
