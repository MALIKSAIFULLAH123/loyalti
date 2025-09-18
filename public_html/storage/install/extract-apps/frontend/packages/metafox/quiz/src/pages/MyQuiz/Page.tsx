/**
 * @type: route
 * name: quiz.my
 * path: /quiz/my
 * chunkName: pages.quiz
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'quiz',
  pageName: 'quiz.my',
  resourceName: 'quiz',
  defaultTab: 'my',
  loginRequired: true
});
