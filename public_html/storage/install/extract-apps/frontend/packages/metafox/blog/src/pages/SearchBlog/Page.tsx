/**
 * @type: route
 * name: blog.search
 * path: /blog/search, /blog/tag/:tag, /blog/category/:category_id/:slug?
 * chunkName: pages.blog
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'blog',
  resourceName: 'blog',
  pageName: 'blog.search',
  categoryName: 'blog_category'
});
