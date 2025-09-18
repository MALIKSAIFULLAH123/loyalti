/**
 * @type: block
 * name: saved.block.savedListAddBlock
 * title: Saved Lists Add
 * keywords: saved
 * description: Display saved collections to add for item saved.
 * thumbnail:
 * experiment: true
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import {
  APP_SAVED,
  PAGINGID_SAVED_LIST_ADD,
  RESOURCE_SAVED_LIST
} from '@metafox/saved/constant';

export default createBlock<ListViewBlockProps>({
  name: 'SavedListAddBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'saved_list'
  },
  defaults: {
    moduleName: APP_SAVED,
    resourceName: RESOURCE_SAVED_LIST,
    actionName: 'viewAll',
    canLoadMore: true,
    emptyPage: 'saved.ui.noCollectionList',
    gridLayout: 'Friend List - Small List',
    itemLayout: 'Collection List',
    itemView: 'saved_collection_list.itemView.addToItem',
    pagingId: PAGINGID_SAVED_LIST_ADD,
    clearDataOnUnMount: true
  }
});
