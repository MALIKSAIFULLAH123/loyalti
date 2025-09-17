<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Traits\Eloquent\Model;

use Exception;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Facades\LoadReduce;

/**
 * Trait HasItemMorph.
 *
 * @mixin HasRelationships
 * @property Content|null $item
 * @property int          $item_id
 * @property string       $item_type
 */
trait HasItemMorph
{
    public function item(): ?MorphTo
    {
        try {
            return $this->morphTo('item', 'item_type', 'item_id');
        } catch (Exception) {
        }

        return null;
    }

    /**
     * @return string
     */
    public function itemType(): string
    {
        return $this->item_type;
    }

    /**
     * @return int
     */
    public function itemId(): int
    {
        return $this->item_id;
    }

    public function getItemAttribute()
    {
        return LoadReduce::getEntity($this->itemType(), $this->itemId(), fn () => $this->getRelationValue('item'));
    }
}
