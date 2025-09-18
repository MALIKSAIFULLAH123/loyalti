/**
 * @type: modalRoute
 * name: blog.viewModal
 * path: /blog/:id(\d+)/:slug?
 * chunkName: pages.blog
 * bundle: web
 */

import { createViewItemModal } from '@metafox/framework';

export default createViewItemModal({
  appName: 'blog',
  resourceName: 'blog',
  pageName: 'blog.viewModal',
  component: 'blog.dialog.blogView'
});
