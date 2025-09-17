<?php

namespace MetaFox\Search\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class HashtagStatistic.
 *
 * @property int $id
 * @property int $tag_id
 * @property int $total_item
 */
class HashtagStatistic extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'search_hashtag_statistic';

    protected $table = 'search_hashtag_statistics';

    /** @var string[] */
    protected $fillable = [
        'tag_id',
        'total_item',
    ];

    public $timestamps = false;

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'tag_id', 'id');
    }
}
