<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Group\Database\Factories\AnswersFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Answers.
 *
 * @property        int            $id
 * @method   static AnswersFactory factory(...$parameters)
 */
class Answers extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'group_answer';

    protected $table = 'group_answers';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'question_id',
        'value',
        'request_id',
    ];

    /**
     * @return AnswersFactory
     */
    protected static function newFactory()
    {
        return AnswersFactory::new();
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class, 'request_id', 'id')->withTrashed();
    }
}

// end
