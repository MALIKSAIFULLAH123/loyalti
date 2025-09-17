/**
 * @type: block
 * name: core.block.AdminStoreApps
 * title: MetaFox Store
 * bundle: admincp
 * experiment: true
 */

import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'app_store_product',
    canLoadMore: true,
    canLoadSmooth: true
  },
  defaults: {
    title: 'All Apps',
    itemView: 'app_store_product.itemView.mainCard',
    blockLayout: 'Admin App Store Block',
    gridLayout: 'App Store Product - Main Cards',
    itemLayout: 'App Store Product - Main Cards',
    emptyPage: 'core.block.no_content_html',
    emptyPageProps: {
      title: 'app_find_more_no_app_found',
      contentStyle: {
        sx: {
          p: 2
        }
      }
    }
  }
});
