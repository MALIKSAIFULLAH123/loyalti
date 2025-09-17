/**
 * @type: route
 * name: featured.add
 * path: /featured/add/:slug?
 * chunkName: pages.featured
 * bundle: web
 */
import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'featured',
  resourceName: 'featured_item',
  pageName: 'featured.add'
});
