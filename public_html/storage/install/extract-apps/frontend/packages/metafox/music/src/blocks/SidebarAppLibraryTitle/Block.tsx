/**
 * @type: block
 * name: music.block.playlistTitle
 * title: Main Card
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock<any>({
  extendBlock: Base,
  defaults: {
    title: 'saved_playlists',
    blockLayout: 'Music Saved Playlists Mobile',
    gridLayout: 'Music Saved Playlists - Small List',
    headerActions: [
      {
        as: 'music.AddPlaylistButton'
      }
    ],
    showWhen: [
      'or',
      ['truthy', 'acl.music.music_playlist.view'],
      ['truthy', 'acl.music.music_playlist.moderate'],
      ['truthy', 'acl.music.music_playlist.create']
    ]
  }
});
