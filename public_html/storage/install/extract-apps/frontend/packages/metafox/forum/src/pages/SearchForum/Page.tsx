/**
 * @type: route
 * name: forum.search
 * path: /forum/search, /forum/tag/:tag
 * chunkName: pages.forum
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'forum',
  resourceName: 'forum',
  pageName: 'forum.search',
  apiParamsResourceDefault: true
});
