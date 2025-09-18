/**
 * @type: route
 * name: advertise.view
 * path: /advertise/:id(\d+)
 * chunkName: pages.advertise
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'advertise',
  resourceName: 'advertise',
  pageName: 'advertise.view'
});
