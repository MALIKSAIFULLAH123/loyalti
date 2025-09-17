<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class GroupText.
 * @mixin Builder
 * @property int    $id
 * @property string $text
 * @property string $text_parsed
 */
class GroupText extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE =  'group_text';

    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'group_text';

    protected $fillable = [
        'id',
        'text',
        'text_parsed',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'id', 'id');
    }
}

// end
