<?php

namespace MetaFox\Blog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class BlogText.
 *
 * @property int    $id
 * @property string $text
 * @property string $text_parsed
 *
 * @mixin Builder
 */
class BlogText extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE = 'blog_text';

    public $timestamps = false;

    public $incrementing = false;

    /**
     * @var string
     */
    protected $table = 'blog_text';

    protected $fillable = [
        'text',
        'text_parsed',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Blog::class, 'id', 'id');
    }
}

// end
