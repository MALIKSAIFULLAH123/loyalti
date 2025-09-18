/**
 * @type: block
 * name: saved.block.SavedItemBlock
 * title: Saved Items
 * keywords: saved
 * description: Display saved items.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import { APP_SAVED } from '@metafox/saved/constant';

export default createBlock<ListViewBlockProps>({
  name: 'SavedItemBlock',
  extendBlock: 'core.block.listview',
  overrides: {},
  defaults: {
    pagingId: 'paging_saved_items',
    title: 'saved_items',
    canLoadSmooth: true,
    itemView: 'saved.itemView.mainCard',
    moduleName: APP_SAVED,
    resourceName: APP_SAVED,
    actionName: 'viewAll'
  }
});
