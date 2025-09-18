/**
 * @type: route
 * name: livestream.edit
 * path: /live-video/edit/:id
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'livestreaming',
  resourceName: 'live_video',
  pageName: 'livestream.edit'
});
