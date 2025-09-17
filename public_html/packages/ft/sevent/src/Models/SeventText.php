<?php

namespace Foxexpert\Sevent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class SeventText.
 *
 * @property int    $id
 * @property string $text
 * @property string $text_parsed
 *
 * @mixin Builder
 */
class SeventText extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE = 'sevent_text';

    public $timestamps = false;

    public $incrementing = false;

    /**
     * @var string
     */
    protected $table = 'sevent_text';

    protected $fillable = [
        'text',
        'text_parsed',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Sevent::class, 'id', 'id');
    }
}

// end
