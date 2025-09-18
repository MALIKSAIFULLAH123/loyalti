<?php

namespace MetaFox\Poll\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Poll\Database\Factories\AnswerFactory;

/**
 * @property string     $answer
 * @property float      $percentage
 * @property int        $total_vote
 * @property int        $ordering
 * @property int        $created_at
 * @property int        $updated_at
 * @property bool       $voted
 * @property Collection $votes
 * @method   static     AnswerFactory factory()
 */
class Answer extends Model implements Entity, HasAmounts
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'poll_answer';

    protected $table = 'poll_answers';

    public $timestamps = false;

    protected $fillable = [
        'poll_id',
        'answer',
        'total_vote',
        'ordering',
        'percentage',
        'created_at',
        'updated_at',
    ];

    public function getPercentageAttribute(float $value): float
    {
        $percentage = round($value, 2);

        return $percentage < 100 ? $percentage : 100;
    }

    protected static function newFactory(): AnswerFactory
    {
        return AnswerFactory::new();
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class, 'poll_id', 'id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Result::class, 'answer_id', 'id');
    }

    public function getVotedAttribute(): bool
    {
        return $this->votes()
            ->where('user_id', '=', Auth::id())
            ->exists();
    }
}
