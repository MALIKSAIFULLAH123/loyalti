/**
 * @type: route
 * name: marketplace.browse
 * path: /marketplace/:tab(all|friend|recent|invite|history|pending|expire|my-pending)
 * chunkName: pages.marketplace
 * bundle: web
 */

import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'marketplace',
  resourceName: 'marketplace',
  pageName: 'marketplace.browse',
  categoryName: 'marketplace_category',
  paramCreator: prev => {
    let view = prev.tab?.replace(/-/g, '_');

    if (view === 'all') {
      view = 'alive';
    }

    return {
      tab: prev.tab?.replace(/-/g, '_'),
      view
    };
  }
});
