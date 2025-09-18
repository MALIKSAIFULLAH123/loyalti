/**
 * @type: route
 * name: music_song.browse
 * path: /music/song, /music/:tab(my|friend|featured|pending|my-pending)
 * chunkName: pages.music
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'music',
  pageName: 'music_song.browse',
  resourceName: 'music_song',
  paramCreator: prev => ({
    tab: `${prev.tab?.replace(/-/g, '_')}_song`,
    view: prev.tab?.replace(/-/g, '_')
  })
});
