<?php

namespace MetaFox\Video\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class VideoText.
 * @mixin Builder
 * @property int    $id
 * @property string $text
 * @property string $text_parsed
 */
class VideoText extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE = 'video_text';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $table = 'video_text';

    protected $fillable = ['id', 'text', 'text_parsed'];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'id', 'id');
    }
}
