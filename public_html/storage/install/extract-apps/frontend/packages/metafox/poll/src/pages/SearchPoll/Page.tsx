/**
 * @type: route
 * name: poll.search
 * path: /poll/search
 * chunkName: pages.poll
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'poll',
  resourceName: 'poll',
  pageName: 'poll.search',
  categoryName: ''
});
