/**
 * @type: route
 * name: pages.claims
 * path:  /pages/claims
 * chunkName: pages.page
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  defaultTab: 'claims',
  appName: 'page',
  resourceName: 'page_claim',
  pageName: 'pages.claims',
  loginRequired: true
});
