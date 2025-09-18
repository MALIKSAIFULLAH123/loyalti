/**
 * @type: route
 * name: blog.browse
 * path: /blog/:tab(friend|all|pending|feature|spam|draft|my-pending)
 * chunkName: pages.blog
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'blog',
  resourceName: 'blog',
  pageName: 'blog.browse',
  categoryName: 'blog_category',
  paramCreator: prev => ({
    tab: prev.tab?.replace(/-/g, '_'),
    view: prev.tab?.replace(/-/g, '_')
  })
});
