/**
 * @type: block
 * name: music.block.playlistBlock
 * keywords: sidebar
 * title: Sidebar Playlist
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'SavedListListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'saved_list',
    loadMoreType: 'button',
    numberOfItemsPerPage: 5,
    pagingId: 'music/myPlaylist',
    errorPage: 'default',
    errorPageProps: {
      hideContent: true
    },
    authRequired: true
  },
  defaults: {
    itemView: 'save_playlist.itemView.mainCard',
    moduleName: 'music',
    resourceName: 'music_playlist',
    actionName: 'viewMy',
    blockLayout: 'Music Saved Playlists',
    gridLayout: 'Music Saved Playlists - Small List',
    loadMoreTypeProp: {
      contentStyle: {
        justifyContent: 'flex-start'
      }
    },
    showWhen: [
      'or',
      ['truthy', 'acl.music.music_playlist.view'],
      ['truthy', 'acl.music.music_playlist.moderate'],
      ['truthy', 'acl.music.music_playlist.create']
    ]
  }
});
