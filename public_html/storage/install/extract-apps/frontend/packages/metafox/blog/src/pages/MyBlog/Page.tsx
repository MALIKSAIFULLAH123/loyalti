/**
 * @type: route
 * name: blog.my
 * path: /blog/my
 * chunkName: pages.blog
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'blog',
  pageName: 'blog.my',
  resourceName: 'blog',
  defaultTab: 'my',
  loginRequired: true
});
