/**
 * @type: route
 * name: event.edit
 * path: /event/edit/:id, /event/add
 * chunkName: pages.event
 * bundle: web
 */
import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'event',
  resourceName: 'event',
  pageName: 'event.edit'
});
