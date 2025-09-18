<?php

namespace MetaFox\Quiz\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class QuizTagData.
 * @mixin Builder
 * @property int    $id
 * @property int    $item_id
 * @property int    $tag_id
 * @property string $tag_text
 */
class QuizTagData extends Pivot
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'quiz_tag_data';

    /**
     * @var string[]
     */
    protected $fillable = [
        'item_id',
        'tag_id',
    ];
}
