/**
 * @type: route
 * name: quiz.search
 * path: /quiz/search
 * chunkName: pages.quiz
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'quiz',
  resourceName: 'quiz',
  pageName: 'quiz.search'
});
