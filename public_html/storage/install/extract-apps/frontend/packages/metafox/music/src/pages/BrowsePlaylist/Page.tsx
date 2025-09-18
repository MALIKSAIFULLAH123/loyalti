/**
 * @type: route
 * name: music.playlist
 * path: /music/playlists, /music/:tab(my|friend|featured|all)-playlists
 * chunkName: pages.music
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'music',
  pageName: 'music_playlist.browse',
  resourceName: 'music_playlist',
  paramCreator: prev => ({
    tab: `${prev.tab}_playlist`
  })
});
