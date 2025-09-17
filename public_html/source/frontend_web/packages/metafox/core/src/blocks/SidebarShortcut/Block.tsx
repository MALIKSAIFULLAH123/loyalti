/**
 * @type: block
 * name: core.block.sidebarShortcutMenu
 * chunkName: sidebarHome
 * title: Shortcut Menu
 * keywords: sidebar
 * chunkName: sidebarHome
 */

import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listviewCollapsible',
  overrides: {
    authRequired: true,
    dataSource: {
      apiUrl: '/user/shortcut',
      apiParams: ''
    },
    pagingId: '/user/shortcut',
    headerActions: [{ as: 'user.ManageShortcutButton' }],
    showWhen: ['truthy', 'acl.user.shortcut.view']
  },
  defaults: {
    gridVariant: 'listView',
    itemView: 'shortcut.itemView.smallCard',
    gridLayout: 'Shortcut - Menu Items',
    blockLayout: 'sidebar shortcut',
    canLoadMore: true,
    canLoadSmooth: true
  }
});
