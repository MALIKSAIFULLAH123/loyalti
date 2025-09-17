<?php

namespace MetaFox\LiveStreaming\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model_text.stub.
 */

/**
 * Class LiveVideo.
 *
 * @property int    $id
 * @property string $text
 * @property string $text_parsed
 *
 * @mixin Builder
 */
class LiveVideoText extends Model implements ResourceText
{
    use HasEntity;

    public $timestamps = false;

    public $incrementing = false;

    public const ENTITY_TYPE = 'livestreaming_text';

    /**
     * @var string
     */
    protected $table = 'livestreaming_text';

    protected $fillable = [
        'text',
        'text_parsed',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(LiveVideo::class, 'id', 'id');
    }
}

// end
