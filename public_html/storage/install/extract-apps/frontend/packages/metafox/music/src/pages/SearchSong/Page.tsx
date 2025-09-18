/**
 * @type: route
 * name: music_song.search
 * path: /music/search, /music/genre/:category(\d+)/:slug?
 * chunkName: pages.music
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'music',
  pageName: 'music_song.search',
  resourceName: 'music',
  categoryName: 'music_genre',
  paramCreator: prev => ({ tab: `${prev.tab}_song` })
});
