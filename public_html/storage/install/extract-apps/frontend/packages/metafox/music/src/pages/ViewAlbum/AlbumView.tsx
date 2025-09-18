/**
 * @type: route
 * name: music_album.view
 * path: /music/album/:id(\d+)/:slug?
 * chunkName: pages.music
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'music',
  resourceName: 'music_album',
  pageName: 'music_album.view'
});
