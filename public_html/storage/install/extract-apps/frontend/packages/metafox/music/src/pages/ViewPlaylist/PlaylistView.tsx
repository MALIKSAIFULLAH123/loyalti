/**
 * @type: route
 * name: music_playlist.view
 * path: /music/playlist/:id(\d+)/:slug?
 * chunkName: pages.music
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'music',
  resourceName: 'music_playlist',
  pageName: 'music_playlist.view'
});
