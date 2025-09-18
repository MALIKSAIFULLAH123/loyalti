<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Saved\Http\Resources\v1\Saved;

use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Resource\ActionItem;
use MetaFox\Platform\Resource\MobileSetting as Setting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Saved Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('homePage')
            ->pageUrl('saved');

        $this->add('viewAll')
            ->pageUrl('saved/all')
            ->apiUrl('saveditems')
            ->apiParams([
                'q'         => ':q',
                'sort_type' => ':sort_type',
                'when'      => ':when',
                'open'      => ':open',
                'type'      => ':type',
                'view'      => ':view',
            ])
            ->apiRules([
                'sort_type' => [
                    'includes', 'sort_type', [Browse::SORT_TYPE_ASC, Browse::SORT_TYPE_DESC],
                ],
                'when'          => ['includes', 'when', ['this_month', 'this_week', 'today']],
                'collection_id' => ['truthy', 'collection_id'],
                'type'          => ['truthy', 'type'],
                'view'          => ['truthy', 'view'],
                'open'          => ['truthy', 'open'],
                'q'             => ['truthy', 'q'],
            ]);

        $this->addDeleteItemAction();

        $this->add('moveItem')
            ->apiUrl('saveditems/collection')
            ->asPut();

        $this->add('markAsOpened')
            ->apiUrl('saveditems/read/:id')
            ->apiParams([
                'status'        => 1,
                'collection_id' => ':collection_id',
            ])
            ->asPatch();

        $this->add('markAsUnOpened')
            ->apiUrl('saveditems/read/:id')
            ->apiParams([
                'status'        => 0,
                'collection_id' => ':collection_id',
            ])
            ->asPatch();

        $this->add('getTabs')
            ->apiUrl('saveditems/get-tab');

        $this->add('addToCollection')
            ->apiUrl('core/mobile/form/saved.saved.add_to_collection/:id');

        $this->add('searchItem')
            ->apiUrl('saveditems')
            ->apiParams([
                'q'         => ':q',
                'sort_type' => ':sort_type',
                'when'      => ':when',
                'open'      => ':open',
                'type'      => ':type',
                'view'      => 'search',
            ])
            ->placeholder(__p('saved::phrase.search_saved_items'));

        $this->add('removeCollectionItem')
            ->apiUrl('saveditems/collection/:list_id/save/:saved_id')
            ->asDelete();

        $this->add('saveItem')
            ->apiUrl('saveditems/save')
            ->asPost();

        $this->addUndoSavedItemAction();
    }

    protected function addDeleteItemAction(): ActionItem
    {
        $deleteItem = $this->add('deleteItem')
            ->apiUrl('saveditems/:id');

        if (Settings::get('saved.enable_unsaved_confirmation')) {
            $deleteItem->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('saved::phrase.delete_confirm'),
                ]
            );
        }

        return $deleteItem;
    }

    protected function addUndoSavedItemAction(): ActionItem
    {
        $undoSavedAction = $this->add('undoSaveItem')
            ->asDelete()
            ->apiUrl('saveditems/unsave')
            ->apiParams([
                'item_type' => ':item_type',
                'item_id'   => ':item_id',
                'like_type' => ':like_type_id',
                'in_feed'   => ':in_feed',
            ]);

        if (Settings::get('saved.enable_unsaved_confirmation')) {
            $undoSavedAction->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('saved::phrase.delete_confirm'),
                ]
            );
        }

        return $undoSavedAction;
    }
}
