/**
 * @type: route
 * name: groups.guestBrowse
 * path: /group/:tab(all)
 * chunkName: pages.group
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'group',
  resourceName: 'group',
  pageName: 'groups.guestBrowse'
});
