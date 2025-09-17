<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Database\Factories\QuestionFieldFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class QuestionField.
 *
 * @property int    $id
 * @property string $title
 * @property int    $question_id
 * @method   static QuestionFieldFactory factory(...$parameters)
 */
class QuestionField extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'group_question_field';

    protected $table = 'group_question_fields';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'title',
        'question_id',
    ];

    /**
     * @return QuestionFieldFactory
     */
    protected static function newFactory(): QuestionFieldFactory
    {
        return QuestionFieldFactory::new();
    }
}

// end
