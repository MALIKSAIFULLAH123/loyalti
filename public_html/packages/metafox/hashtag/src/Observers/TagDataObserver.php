<?php

namespace MetaFox\Hashtag\Observers;

use MetaFox\Hashtag\Models\TagData;
use MetaFox\Platform\Contracts\HasAmounts;

/**
 * Class TagDataObserver.
 */
class TagDataObserver
{
    /**
     * @param TagData $tagData
     *
     * @return void
     */
    public function created(TagData $tagData)
    {
        $tag = $tagData->tag;
        if ($tag instanceof HasAmounts) {
            $tag->incrementAmount('total_item');
        }
    }

    /**
     * @param TagData $tagData
     *
     * @return void
     */
    public function deleted(TagData $tagData)
    {
        $tag = $tagData->tag;
        if ($tag instanceof HasAmounts) {
            $tag->decrementAmount('total_item');
        }
    }
}
