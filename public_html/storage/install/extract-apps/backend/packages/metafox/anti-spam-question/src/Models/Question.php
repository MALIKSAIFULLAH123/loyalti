<?php

namespace MetaFox\AntiSpamQuestion\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class Question
 *
 * @property int        $id
 * @property string     $question
 * @property int        $image_file_id
 * @property boolean    $is_active
 * @property boolean    $is_case_sensitive
 * @property int        $ordering
 * @property string     $created_at
 * @property string     $updated_at
 * @property Collection $answers
 */
class Question extends Model implements Entity,
    HasThumbnail,
    HasTitle
{
    use HasEntity;
    use HasFactory;
    use HasThumbnailTrait;
    use HasTranslatableAttributes;

    public const ENTITY_TYPE = 'asq_question';

    protected $table = 'asq_questions';

    // where to store resources ?
    public array $fileColumns = [
        'image_file_id' => 'photo',
    ];

    protected $translatableAttributes = [
        'question',
    ];

    /** @var string[] */
    protected $fillable = [
        'question',
        'image_file_id',
        'is_active',
        'is_case_sensitive',
        'ordering',
        'created_at',
        'updated_at',
    ];

    public function toTitle(): string
    {
        return __p($this->question);
    }

    public function getThumbnail(): ?string
    {
        return $this->image_file_id;
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'question_id', 'id');
    }
}

// end
