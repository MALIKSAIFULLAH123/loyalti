/**
 * @type: route
 * name: music_album.search
 * path: /music/album/search
 * chunkName: pages.music
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'music',
  pageName: 'music_album.search',
  resourceName: 'music_album',
  paramCreator: prev => ({ tab: `${prev.tab}_album` })
});
