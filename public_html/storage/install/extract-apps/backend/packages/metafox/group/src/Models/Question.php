<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use MetaFox\Group\Database\Factories\QuestionFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Question.
 *
 * @property        int               $id
 * @property        string            $question
 * @property        int               $group_id
 * @property        int               $type_id
 * @property        Group             $group
 * @property        Collection        $questionFields
 * @property        Collection        $answers
 * @property        array<int, mixed> $questionFieldsForFE
 * @method   static QuestionFactory   factory(...$parameters)
 */
class Question extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE       = 'group_question';
    public const TYPE_TEXT         = 0;
    public const TYPE_SELECT       = 1;
    public const TYPE_MULTI_SELECT = 2;
    public const MAX_QUESTION      = 3;
    public const MIN_OPTION        = 2;

    protected $table = 'group_questions';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'question',
        'group_id',
        'type_id',
    ];

    /**
     * @return QuestionFactory
     */
    protected static function newFactory(): QuestionFactory
    {
        return QuestionFactory::new();
    }

    public function questionFields(): HasMany
    {
        return $this->hasMany(QuestionField::class, 'question_id', 'id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answers::class, 'question_id', 'id');
    }

    public function group(): HasOne
    {
        return $this->hasOne(Group::class, 'id', 'group_id')->withTrashed();
    }

    /**
     * @return array<int, mixed>
     */
    public function getQuestionFieldsForFEAttribute(): array
    {
        $fields = [];
        $this->questionFields()
            ->orderBy('id')
            ->each(function (QuestionField $field) use (&$fields) {
                $fields[] = [
                    'id'    => $field->entityId(),
                    'title' => ban_word()->clean($field->title),
                ];
            });

        return $fields;
    }
}

// end
