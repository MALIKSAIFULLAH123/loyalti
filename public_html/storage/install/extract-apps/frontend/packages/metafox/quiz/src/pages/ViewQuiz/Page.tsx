/**
 * @type: route
 * name: quiz.view
 * path: /quiz/:id(\d+)/:slug?
 * chunkName: pages.quiz
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'quiz',
  resourceName: 'quiz',
  pageName: 'quiz.view'
});
