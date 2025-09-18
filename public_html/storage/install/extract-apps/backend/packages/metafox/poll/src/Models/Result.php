<?php

namespace MetaFox\Poll\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Poll\Database\Factories\ResultFactory;
use MetaFox\Poll\Notifications\PollResultNotification;
use MetaFox\Poll\Policies\PollPolicy;

/**
 * class Result.
 * @mixin Builder
 * @property        Poll          $poll
 * @property        Answer        $answer
 * @property        string        $created_at
 * @property        string        $updated_at
 * @property        bool          $is_last
 * @method   static ResultFactory factory()
 */
class Result extends Model implements Entity, IsNotifyInterface
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'poll_result';

    protected $table = 'poll_results';

    protected $fillable = [
        'poll_id',
        'answer_id',
        'user_id',
        'user_type',
        'is_last',
    ];

    protected $casts = [
        'is_last' => 'boolean',
    ];

    public function toNotification(): ?array
    {
        $context = user();

        $notification = new PollResultNotification($this);

        $user = $this->poll?->user;

        if (!$this->is_last) {
            return null; // Only send notification on the last vote
        }

        if (!$user) {
            return null;
        }

        if (!$user instanceof IsNotifiable) {
            return null;
        }

        if ($user->entityId() === $context->entityId()) {
            return null;
        }

        if (!policy_check(PollPolicy::class, 'view', $user, $this->poll)) {
            return null;
        }

        return [$user, $notification];
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class, 'poll_id', 'id');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'answer_id', 'id');
    }

    /**
     * @param  array<string, mixed> $parameters
     * @return ResultFactory
     */
    public static function newFactory(array $parameters = []): ResultFactory
    {
        return ResultFactory::new($parameters);
    }

    public function getTotalMutual(): int
    {
        return (int) app('events')->dispatch(
            'friend.count_total_mutual_friend',
            [user()->id, $this->user_id],
            true
        );
    }
}
