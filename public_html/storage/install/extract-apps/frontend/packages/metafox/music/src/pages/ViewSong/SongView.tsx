/**
 * @type: route
 * name: music_song.view
 * path: /music/:id(\d+)/:slug?, /music/song/:id(\d+)/:slug?
 * chunkName: pages.music
 * bundle: web
 */

import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'music',
  resourceName: 'music_song',
  pageName: 'music_song.view'
});
