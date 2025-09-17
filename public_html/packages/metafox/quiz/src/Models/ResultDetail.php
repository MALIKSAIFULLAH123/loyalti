<?php

namespace MetaFox\Quiz\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Quiz\Database\Factories\ResultDetailFactory;

/**
 * Class ResultDetail.
 * @property int      $id
 * @property Result   $result
 * @property Question $question
 * @property Answer   $answer
 * @property int      $is_correct
 * @method   static   ResultDetailFactory factory()
 */
class ResultDetail extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'quiz_result_item';

    protected $table = 'quiz_result_items';

    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $with = ['question', 'answer'];

    /** @var string[] */
    protected $fillable = [
        'result_id',
        'question_id',
        'answer_id',
        'is_correct',
    ];

    /**
     * @return ResultDetailFactory
     */
    protected static function newFactory(): ResultDetailFactory
    {
        return ResultDetailFactory::new();
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class, 'result_id', 'id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'answer_id', 'id');
    }
}

// end
