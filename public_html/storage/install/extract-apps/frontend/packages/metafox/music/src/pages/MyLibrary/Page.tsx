/**
 * @type: route
 * name: music.library
 * path: /music/my-library
 * chunkName: pages.music
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'music',
  resourceName: 'music_song',
  pageName: 'music.library',
  defaultTab: 'my_library',
  loginRequired: true
});
