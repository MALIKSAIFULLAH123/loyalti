/**
 * @type: route
 * name: quiz.home
 * path: /quiz
 * chunkName: pages.quiz
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'quiz',
  pageName: 'quiz.home',
  resourceName: 'quiz'
});
