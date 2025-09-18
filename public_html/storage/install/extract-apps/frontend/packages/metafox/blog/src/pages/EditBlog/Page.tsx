/**
 * @type: route
 * name: blog.edit
 * path: /blog/edit/:id,/blog/add
 * chunkName: pages.blog
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'blog',
  resourceName: 'blog',
  pageName: 'blog.edit'
});
