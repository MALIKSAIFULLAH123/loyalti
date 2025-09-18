/**
 * @type: route
 * name: event.view
 * path: /event/:id(\d+)/:slug?
 * chunkName: pages.event
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'event',
  resourceName: 'event',
  pageName: 'event.view',
  paramCreator: prev => ({
    profile_id: prev?.id
  })
});
