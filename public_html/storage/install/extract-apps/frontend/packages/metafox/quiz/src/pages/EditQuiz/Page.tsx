/**
 * @type: route
 * name: quiz.edit
 * path: /quiz/edit/:id?, /quiz/add
 * chunkName: pages.quiz
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'quiz',
  resourceName: 'quiz',
  pageName: 'quiz.edit'
});
