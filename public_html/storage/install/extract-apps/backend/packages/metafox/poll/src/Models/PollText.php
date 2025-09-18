<?php

namespace MetaFox\Poll\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class PollText.
 * @mixin Builder
 * @property int    $id
 * @property string $text
 * @property string $text_parsed
 */
class PollText extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE = 'poll_text';

    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'poll_text';

    protected $fillable = [
        'id',
        'text',
        'text_parsed',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Poll::class, 'id', 'id');
    }
}

// end
