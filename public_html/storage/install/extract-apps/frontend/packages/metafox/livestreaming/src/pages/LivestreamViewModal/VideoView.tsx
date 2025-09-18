/**
 * @type: modalRoute
 * name: livestreaming.viewModal
 * path: /live-video/:id(\d+)
 * bundle: web
 */
import { createViewItemModal } from '@metafox/framework';

export default createViewItemModal({
  appName: 'livestreaming',
  resourceName: 'live_video',
  pageName: 'livestreaming.viewModal',
  component: 'livestreaming.dialog.videoView',
  dialogId: 'viewLivestreaming'
});
