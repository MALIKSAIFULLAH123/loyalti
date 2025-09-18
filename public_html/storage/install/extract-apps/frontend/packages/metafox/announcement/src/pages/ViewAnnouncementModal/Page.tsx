/**
 * @type: modalRoute
 * name: announcement.viewModal
 * path: /announcement/:id(\d+)/:slug?
 * chunkName: pages.announcement
 * bundle: web
 */

import { createViewItemModal } from '@metafox/framework';

export default createViewItemModal({
  appName: 'announcement',
  resourceName: 'announcement',
  pageName: 'announcement.viewModal',
  component: 'announcement.dialog.ViewAnnouncement'
});
