/**
 * @type: route
 * name: music_song.edit
 * path: /music/song/edit/:id
 * chunkName: pages.music
 * bundle: web
 */
import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'music',
  resourceName: 'music_song',
  pageName: 'music_song.edit'
});
