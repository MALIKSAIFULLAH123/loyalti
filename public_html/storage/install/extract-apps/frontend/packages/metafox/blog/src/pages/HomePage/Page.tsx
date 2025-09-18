/**
 * @type: route
 * name: blog.home
 * path: /blog
 * chunkName: pages.blog
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'blog',
  pageName: 'blog.home',
  resourceName: 'blog'
});
