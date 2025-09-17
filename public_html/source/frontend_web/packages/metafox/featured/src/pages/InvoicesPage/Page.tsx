/**
 * @type: route
 * name: featured.invoice
 * path: /featured/invoice
 * chunkName: pages.featured
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'featured',
  pageName: 'featured.invoice',
  resourceName: 'featured_invoice',
  defaultTab: 'invoice',
  loginRequired: true
});
