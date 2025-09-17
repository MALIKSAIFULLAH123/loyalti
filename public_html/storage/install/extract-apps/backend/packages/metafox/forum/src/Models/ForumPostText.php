<?php

namespace MetaFox\Forum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

class ForumPostText extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE = 'forum_post_text';

    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'forum_post_text';

    protected $fillable = [
        'id',
        'text',
        'text_parsed',
    ];

    /**
     * @return BelongsTo
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class, 'id');
    }
}
