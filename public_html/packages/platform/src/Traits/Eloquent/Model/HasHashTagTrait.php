<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Traits\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Hashtag\Models\TagData;
use MetaFox\Platform\Contracts\HasHashTag;

/**
 * Trait HasHashTagTrait.
 *
 * @mixin HasHashTag
 */
trait HasHashTagTrait
{
    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'hashtag_tag_data',
            'item_id',
            'tag_id'
        )->wherePivot('item_type', '=', $this->entityType())
            ->using(TagData::class);
    }
}
