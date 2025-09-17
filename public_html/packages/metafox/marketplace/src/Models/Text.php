<?php

namespace MetaFox\Marketplace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Listing Text.
 *
 * @property int    $id
 * @property string $text
 * @property string $text_parsed
 *
 * @mixin Builder
 */
class Text extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE =  'marketplace_listing_text';

    public $timestamps = false;

    public $incrementing = false;

    /**
     * @var string
     */
    protected $table = 'marketplace_listing_text';

    protected $fillable = [
        'id',
        'text',
        'text_parsed',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'id', 'id');
    }
}
