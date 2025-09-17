<?php

namespace MetaFox\Quiz\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Quiz\Database\Factories\QuestionFactory;

/**
 * class Question.
 *
 * @property        string          $question
 * @property        string          $moduleName
 * @property        int             $ordering
 * @property        Quiz            $quiz
 * @property        Collection      $answers
 * @method   static QuestionFactory factory()
 */
class Question extends Model implements Entity
{
    use HasEntity;
    use HasNestedAttributes;
    use HasFactory;

    public const ENTITY_TYPE = 'quiz_question';

    protected $table = 'quiz_questions';

    public $timestamps = false;

    protected $fillable = [
        'quiz_id',
        'ordering',
        'question',
    ];

    public static function newFactory(): QuestionFactory
    {
        return QuestionFactory::new();
    }

    public function answers(): hasMany
    {
        return $this->hasMany(Answer::class, 'question_id', 'id')->orderBy('id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'id');
    }
}
