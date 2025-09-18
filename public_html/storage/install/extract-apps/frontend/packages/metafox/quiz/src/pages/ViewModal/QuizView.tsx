/**
 * @type: modalRoute
 * name: quiz.viewModal
 * path: /quiz/:id(\d+)/:slug?
 * chunkName: pages.quiz
 * bundle: web
 */

import { createViewItemModal } from '@metafox/framework';

export default createViewItemModal({
  appName: 'quiz',
  resourceName: 'quiz',
  pageName: 'quiz.viewModal',
  component: 'quiz.dialog.quizView'
});
