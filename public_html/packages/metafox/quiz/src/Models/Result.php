<?php

namespace MetaFox\Quiz\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Quiz\Database\Factories\ResultFactory;
use MetaFox\Quiz\Notifications\SubmitResultNotifications;

/**
 * Class Result.
 *
 * @mixin Builder
 * @property int        $id
 * @property int        $quiz_id
 * @property int        $total_correct
 * @property int        $items_count
 * @property string     $created_at
 * @property string     $updated_at
 * @property Quiz       $quiz
 * @property Collection $items
 * @property User       $user
 * @method   static     ResultFactory factory()
 */
class Result extends Model implements Entity, IsNotifyInterface
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'quiz_result';

    protected $table = 'quiz_results';

    /**
     * @var string[]
     */
    protected $withCount = ['items'];

    /** @var string[] */
    protected $fillable = [
        'quiz_id',
        'user_id',
        'user_type',
        'total_correct',
        'updated_at',
        'created_at',
    ];

    /**
     * @return ResultFactory
     */
    protected static function newFactory(): ResultFactory
    {
        return ResultFactory::new();
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ResultDetail::class, 'result_id', 'id');
    }

    public function toNotification(): ?array
    {
        $context = user();

        $user = $this->quiz->user;

        if ($user->entityId() === $context->entityId()) {
            return null;
        }

        return [$user, new SubmitResultNotifications($this)];
    }
}

// end
