<?php

namespace MetaFox\Quiz\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Quiz\Database\Factories\AnswerFactory;

/**
 * class Answer.
 *
 * @property string   $answer
 * @property string   $moduleName
 * @property int      $ordering
 * @property int      $is_correct
 * @property Question $question
 * @method   static   AnswerFactory factory()
 */
class Answer extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'quiz_answer';

    protected $table = 'quiz_answers';

    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'ordering',
        'answer',
        'is_correct',
        'total_play',
    ];

    public static function newFactory(): AnswerFactory
    {
        return AnswerFactory::new();
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }
}
