/**
 * @type: route
 * name: poll.edit
 * path: /poll/edit/:id?, /poll/add, /poll/add/:slug?
 * chunkName: pages.poll
 * bundle: web
 */
import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'poll',
  resourceName: 'poll',
  pageName: 'poll.edit'
});
