<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Database\Factories\CancelFeedbackFactory;
use MetaFox\User\Models\CancelReason;

/**
 * Class CancelFeedback.
 *
 * @property int                   $id
 * @property string                $email
 * @property string                $name
 * @property string                $phone_number
 * @property string                $user_group_id
 * @property string                $feedback_text
 * @property array                 $extra
 * @property string                $created_at
 * @property string                $updated_at
 * @property CancelReason          $reason
 * @property Role|null             $role
 * @method   CancelFeedbackFactory factory(...$parameters)
 */
class CancelFeedback extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'user_cancel_feedback';

    protected $table = 'user_delete_feedback';

    protected $casts = [
        'extra' => 'array',
    ];

    /** @var string[] */
    protected $fillable = [
        'email',
        'name',
        'user_group_id',
        'feedback_text',
        'reason_id',
        'reasons_given',
        'user_id',
        'user_type',
        'phone_number',
        'extra',
        'created_at',
        'updated_at',
    ];

    /**
     * @return CancelFeedbackFactory
     */
    protected static function newFactory()
    {
        return CancelFeedbackFactory::new();
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(CancelReason::class, 'reason_id', 'id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'user_group_id', 'id');
    }
}

// end
