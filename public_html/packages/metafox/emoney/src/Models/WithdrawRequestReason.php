<?php

namespace MetaFox\EMoney\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\EMoney\Database\Factories\WithdrawRequestReasonFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class WithdrawRequestReason.
 *
 * @property int    $id
 * @property int    $request_id
 * @property string $type
 * @property string $message
 * @property string $created_at
 * @property string $updated_at
 *
 * @method static WithdrawRequestReasonFactory factory(...$parameters)
 */
class WithdrawRequestReason extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'ewallet_withdraw_request_reason';

    protected $table = 'emoney_withdraw_request_reasons';

    /** @var string[] */
    protected $fillable = [
        'request_id',
        'type',
        'message',
        'created_at',
        'updated_at',
    ];

    /**
     * @return WithdrawRequestReasonFactory
     */
    protected static function newFactory()
    {
        return WithdrawRequestReasonFactory::new();
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(WithdrawRequest::class, 'request_id');
    }
}

// end
