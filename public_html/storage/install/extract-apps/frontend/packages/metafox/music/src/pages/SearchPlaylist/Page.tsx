/**
 * @type: route
 * name: music_playlist.search
 * path: /music/playlist/search
 * chunkName: pages.music
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'music',
  pageName: 'music_playlist.search',
  resourceName: 'music_playlist',
  paramCreator: prev => ({
    tab: `${prev.tab}_playlist`
  })
});
