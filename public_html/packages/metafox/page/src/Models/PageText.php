<?php

namespace MetaFox\Page\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class PageText.
 * @mixin Builder
 * @property int $id
 */
class PageText extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE = 'page_text';

    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'page_text';

    protected $fillable = [
        'id',
        'text',
        'text_parsed',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'id', 'id');
    }
}
