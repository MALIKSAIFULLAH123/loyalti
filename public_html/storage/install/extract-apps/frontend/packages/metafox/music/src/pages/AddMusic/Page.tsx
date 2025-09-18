/**
 * @type: route
 * name: music.add
 * path: /music/add
 * chunkName: pages.music
 * bundle: web
 */
import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'music',
  resourceName: 'music_song',
  pageName: 'music.add'
});
