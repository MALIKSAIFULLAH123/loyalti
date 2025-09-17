<?php

namespace MetaFox\Photo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class PhotoInfo.
 * @property int    $id
 * @property string $text
 * @property string $text_parsed
 * @property string $tagged_friends
 */
class PhotoInfo extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE = 'photo_info';

    protected $table = 'photo_info';

    public $incrementing = false;

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'id',
        'text',
        'text_parsed',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Photo::class, 'id', 'id');
    }
}
