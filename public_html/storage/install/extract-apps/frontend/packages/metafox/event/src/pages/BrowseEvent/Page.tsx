/**
 * @type: route
 * name: event.browse
 * path: /event/:tab(pending|invites|my-pending)
 * chunkName: pages.event
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'event',
  resourceName: 'event',
  pageName: 'event.browse',
  categoryName: 'event_category',
  paramCreator: prev => ({
    tab: prev.tab?.replace(/-/g, '_'),
    view: prev.tab?.replace(/-/g, '_')
  })
});
