<?php

namespace MetaFox\ActivityPoint\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Support\Facade\PointConversion;
use MetaFox\ActivityPoint\Support\PointConversion as Support;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class ConversionRequest
 *
 * @property int                      $id
 * @property ConversionStatistic|null $statistic
 * @property int                      $points
 * @property bool                     $is_approved
 * @property bool                     $is_pending
 * @property bool                     $is_denied
 * @property bool                     $is_cancelled
 */
class ConversionRequest extends Model implements Entity, HasUrl
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'activitypoint_conversion_request';

    protected $table = 'apt_conversion_requests';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'points',
        'currency',
        'total',
        'commission',
        'actual',
        'status',
        'denied_reason',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'points'     => 'integer',
        'total'      => 'float',
        'commission' => 'float',
        'actual'     => 'float',
    ];

    public function getIsPendingAttribute(): bool
    {
        return $this->getAttributeFromArray('status') == Support::TRANSACTION_STATUS_PENDING;
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->getAttributeFromArray('status') == Support::TRANSACTION_STATUS_APPROVED;
    }

    public function getIsDeniedAttribute(): bool
    {
        return $this->getAttributeFromArray('status') == Support::TRANSACTION_STATUS_DENIED;
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->getAttributeFromArray('status') == Support::TRANSACTION_STATUS_CANCELLED;
    }

    public function getStatusTextAttribute(): string
    {
        $status = $this->getAttributeFromArray('status');

        $options = PointConversion::getConversionRequestStatusOptions();

        $options = array_combine(array_column($options, 'value'), array_column($options, 'label'));

        return Arr::get($options, $status, __p('activitypoint::phrase.unknown'));
    }

    public function getTotalTextAttribute(): string
    {
        return app('currency')->getPriceFormatByCurrencyId($this->getAttributeFromArray('currency'), $this->getAttributeFromArray('total'));
    }

    public function getCommissionTextAttribute(): string
    {
        return app('currency')->getPriceFormatByCurrencyId($this->getAttributeFromArray('currency'), $this->getAttributeFromArray('commission'));
    }

    public function getActualTextAttribute(): string
    {
        return app('currency')->getPriceFormatByCurrencyId($this->getAttributeFromArray('currency'), $this->getAttributeFromArray('actual'));
    }

    public function statistic(): BelongsTo
    {
        return $this->belongsTo(ConversionStatistic::class, 'user_id', 'user_id');
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('activitypoint/conversion-request?id=' . $this->entityId());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('activitypoint/conversion-request?id=' . $this->entityId());
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiResourceUrl($this->entityType(), $this->entityId());
    }

    public function toAdminCPUrl(): string
    {
        return url_utility()->makeApiFullUrl('admincp/activitypoint/conversion-request/browse?id=' . $this->entityId());
    }
}
