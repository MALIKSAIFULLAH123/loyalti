<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Contracts\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph as HasItemMorphModel;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class ItemStatistics.
 *
 * @property int $id
 * @property int $total_pending
 * @property int $total_tag_friend
 * @property int $total_mention
 * @property int $total_pending_comment
 * @property int $total_pending_reply
 */
class ItemStatistics extends Model implements
    HasItemMorph,
    HasAmounts,
    Entity
{
    use HasEntity;
    use HasItemMorphModel;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'core_item_statistics';

    public const PENDING_COMMENT = 'total_pending_comment';

    public const PENDING_REPLY = 'total_pending_reply';

    protected $table = 'core_item_statistics';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'item_type',
        'total_pending_comment',
        'total_pending_reply',
        'total_mention',
        'total_tag_friend',
    ];

    public function item(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'item_type', 'item_id');
    }
}
