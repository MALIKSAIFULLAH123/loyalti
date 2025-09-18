/**
 * @type: block
 * name: saved.block.savedListListingBlock
 * title: Saved Lists Collections
 * keywords: saved
 * description: Display saved collections.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import { APP_SAVED, RESOURCE_SAVED_LIST } from '@metafox/saved/constant';

export default createBlock<ListViewBlockProps>({
  name: 'SavedListListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'saved_list'
  },
  defaults: {
    title: 'My Collections',
    moduleName: APP_SAVED,
    resourceName: RESOURCE_SAVED_LIST,
    actionName: 'viewAll',
    loadMoreType: 'button',
    canLoadMore: true,
    canLoadSmooth: true,
    numberOfItemsPerPage: 5,
    itemView: 'saved_collection_list.itemView.mainCard',
    loadMoreTypeProp: {
      contentStyle: {
        pl: 1,
        justifyContent: 'flex-start'
      }
    }
  }
});
