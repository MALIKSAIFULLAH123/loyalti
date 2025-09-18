/**
 * @type: block
 * name: music_playlist.block.playlistViewMobile
 * title: Playlist Detail Mobile
 * keywords: music
 * description: Display playlist detail mobile
 */

import { connectSubject, createBlock } from '@metafox/framework';
import connectSongItem from '../../../containers/connectSongItem';
import Base, { Props } from './Base';

const Enhance = connectSubject(connectSongItem(Base));

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    placeholder: 'Search',
    blockProps: {
      variant: 'plained',
      titleComponent: 'h2',
      titleVariant: 'subtitle1',
      titleColor: 'textPrimary',
      noFooter: true,
      noHeader: true,
      blockStyle: {},
      contentStyle: {
        borderRadius: 'base'
      },
      headerStyle: {},
      footerStyle: {}
    }
  }
});
