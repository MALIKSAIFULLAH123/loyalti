<?php

namespace MetaFox\Page\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Page\Database\Factories\BlockFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Block.
 *
 * @property int    $id
 * @method   static BlockFactory factory(...$parameters)
 * @property Page   $page
 * @property mixed  $page_id
 */
class Block extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;

    public const ENTITY_TYPE = 'page_block';

    protected $table = 'page_blocks';

    /** @var string[] */
    protected $fillable = [
        'id',
        'page_id',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id', 'id')->withTrashed();
    }
}
