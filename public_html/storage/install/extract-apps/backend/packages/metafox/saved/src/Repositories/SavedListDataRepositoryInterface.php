<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Saved\Repositories;

use MetaFox\Platform\Contracts\Content;

/**
 * Interface SavedListDataRepositoryInterface.
 */
interface SavedListDataRepositoryInterface
{
    /**
     * Delete all items.
     *
     * @param Content $item
     *
     * @return void
     */
    public function deleteForItem(Content $item);

    /**
     * @param  int   $itemId
     * @return array
     */
    public function getCollectionByItem(int $itemId): array;
}
