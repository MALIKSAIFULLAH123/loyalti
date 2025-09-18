/**
 * @type: route
 * name: quiz.browse
 * path: /quiz/:tab(all|friend|pending|feature|sponsor|my-pending)
 * chunkName: pages.quiz
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'quiz',
  resourceName: 'quiz',
  pageName: 'quiz.browse',
  paramCreator: prev => ({
    tab: prev.tab?.replace(/-/g, '_'),
    view: prev.tab?.replace(/-/g, '_')
  })
});
