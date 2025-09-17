<?php

namespace MetaFox\Mfa\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Mfa\Database\Factories\EnforceRequestFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class EnforceRequest.
 *
 * @property        int    $id
 * @property        int    $user_id
 * @property        string $user_type
 * @property        int    $is_active
 * @property        string $created_at
 * @property        string $modified_at
 * @property        string $due_at
 * @method   static EnforceRequestFactory factory(...$parameters)
 */
class EnforceRequest extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'mfa_enforce_request';

    public const STATUS_SUCCESS   = 'success';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FORCED    = 'forced';
    public const STATUS_BLOCKED   = 'blocked';

    protected $table = 'mfa_enforce_requests';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'is_active',
        'enforce_status',
        'created_at',
        'modified_at',
        'due_at',
    ];

    /**
     * @return EnforceRequestFactory
     */
    protected static function newFactory()
    {
        return EnforceRequestFactory::new();
    }

    /**
     * Check whether this request passed its due date.
     * @return bool
     */
    public function isOverdue(): bool
    {
        return $this->due_at && Carbon::now()->greaterThan($this->due_at);
    }

    /**
     * Mark this request as success.
     *
     * @return bool
     */
    public function onSuccess(): bool
    {
        $this->enforce_status = static::STATUS_SUCCESS;
        $this->is_active = 0;

        return $this->save();
    }

    /**
     * Mark this request as cancelled.
     *
     * @return bool
     */
    public function onCancelled(): bool
    {
        $this->enforce_status = static::STATUS_CANCELLED;
        $this->is_active = 0;

        return $this->save();
    }

    /**
     * Mark this request as forced.
     *
     * @return bool
     */
    public function onForced(): bool
    {
        $this->enforce_status = static::STATUS_FORCED;
        $this->is_active = 0;

        return $this->save();
    }

    /**
     * Mark this request as blocked.
     *
     * @return bool
     */
    public function onBlocked(): bool
    {
        $this->enforce_status = static::STATUS_BLOCKED;
        $this->is_active = 0;

        return $this->save();
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        return '';
    }

    public function getAdminEditUrlAttribute(): string
    {
        return '';
    }

    /**
     * Get reminder schedules for this request.
     * @return array
     */
    public function getReminderSchedule(): array
    {
        $reminders = [];
        $reminderInterval = [1, 3, 5, 7, 30]; // x days before the expiration

        $createdAt = Carbon::make($this->created_at);
        $dueDate = Carbon::make($this->due_at);

        foreach ($reminderInterval as $interval) {
            $remindDate = $dueDate->copy()->subDays($interval);
            if ($createdAt->gt($remindDate)) {
                break;
            }

            array_push($reminders, $remindDate->toIso8601String());
        }

        return $reminders;
    }

    /**
     * Get remaining days.
     * @return int
     */
    public function getRemainingDays(): int
    {
        $diff = Carbon::make($this->due_at)->diffInDays();
        if ($diff < 0) {
            return 0;
        }

        return $diff;
    }
}

// end
