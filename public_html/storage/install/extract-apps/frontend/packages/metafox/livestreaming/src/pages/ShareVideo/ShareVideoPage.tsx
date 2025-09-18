/**
 * @type: route
 * name: livestreaming.add
 * path: /live-video/add
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'livestreaming',
  resourceName: 'live_video',
  pageName: 'livestreaming.add'
});
