/**
 * @type: route
 * name: music_album.edit
 * path: /music/album/edit/:id
 * chunkName: pages.music
 * bundle: web
 */
import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'music',
  resourceName: 'music_album',
  pageName: 'music_album.edit'
});
