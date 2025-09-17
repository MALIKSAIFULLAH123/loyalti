<?php

namespace MetaFox\AntiSpamQuestion\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class Answer
 *
 * @property int    $id
 * @property int    $question_id
 * @property string $answer
 * @property int    $ordering
 * @property string $created_at
 * @property string $updated_at
 */
class Answer extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'asq_answer';

    protected $table      = 'asq_answers';
    public    $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'question_id',
        'answer',
        'ordering',
    ];

    public function toTitle(): string
    {
        return $this->answer;
    }
}

// end
