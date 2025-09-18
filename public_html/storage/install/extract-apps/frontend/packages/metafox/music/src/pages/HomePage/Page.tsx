/**
 * @type: route
 * name: music.home
 * path: /music
 * chunkName: pages.music
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'music',
  pageName: 'music.home',
  resourceName: 'music_song'
});
