/**
 * @type: modalRoute
 * name: poll.viewModal
 * path: /poll/:id(\d+)/:slug?
 * bundle: web
 */

import { createViewItemModal } from '@metafox/framework';

export default createViewItemModal({
  appName: 'poll',
  resourceName: 'poll',
  pageName: 'poll.viewModal',
  component: 'poll.dialog.pollView'
});
