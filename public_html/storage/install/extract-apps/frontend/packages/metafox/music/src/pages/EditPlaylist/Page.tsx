/**
 * @type: route
 * name: music_playlist.edit
 * path: /music/playlist/edit/:id, /music/playlist/add
 * chunkName: pages.music
 * bundle: web
 */
import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'music',
  resourceName: 'music_playlist',
  pageName: 'music_playlist.edit'
});
