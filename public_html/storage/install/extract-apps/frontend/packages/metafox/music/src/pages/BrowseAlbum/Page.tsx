/**
 * @type: route
 * name: music_album.browse
 * path: /music/albums, /music/:tab(my|friend|featured)-albums
 * chunkName: pages.music
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'music',
  pageName: 'music_album.browse',
  resourceName: 'music_album',
  paramCreator: prev => ({ tab: `${prev.tab}_album` })
});
