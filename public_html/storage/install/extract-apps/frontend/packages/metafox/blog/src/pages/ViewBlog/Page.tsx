/**
 * @type: route
 * name: blog.view
 * path: /blog/:id(\d+)/:slug?
 * chunkName: pages.blog
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'blog',
  resourceName: 'blog',
  pageName: 'blog.view'
});
