/**
 * @type: modalRoute
 * name: video.viewModal
 * path: /video/play/:id/:slug?
 * bundle: web
 */
import { createViewItemModal } from '@metafox/framework';

export default createViewItemModal({
  appName: 'video',
  resourceName: 'video',
  pageName: 'video.viewModal',
  component: 'video.dialog.videoView',
  dialogId: 'viewMedia'
});
