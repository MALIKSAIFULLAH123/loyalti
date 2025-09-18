<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Saved\Repositories\Eloquent;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Saved\Models\SavedListData;
use MetaFox\Saved\Repositories\SavedListDataRepositoryInterface;

/**
 * Class SavedListRepository.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class SavedListDataRepository extends AbstractRepository implements SavedListDataRepositoryInterface
{
    public function model()
    {
        return SavedListData::class;
    }

    /**
     * @param Content $item
     */
    public function deleteForItem(Content $item)
    {
        $this->deleteWhere(['item_id' => $item->entityId(), 'item_type' => $item->entityType()]);
    }

    /**
     * @inheritDoc
     */
    public function getCollectionByItem(int $itemId): array
    {
        return $this->getModel()->newQuery()
            ->where('save_id', $itemId)
            ->pluck('list_id')->toArray();
    }
}
