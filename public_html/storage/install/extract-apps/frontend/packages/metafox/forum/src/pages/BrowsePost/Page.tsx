/**
 * @type: route
 * name: forum_post.browse
 * path: /forum/:tab(pending|my-pending)-post
 * chunkName: pages.forum
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'forum',
  resourceName: 'forum_post',
  pageName: 'forum_post.browse',
  paramCreator: prev => ({
    tab: `${prev.tab?.replace(/-/g, '_')}_post`,
    view: prev.tab?.replace(/-/g, '_')
  })
});
