/**
 * @type: block
 * name: music_playlist.block.playlistView
 * title: Playlist Detail
 * keywords: music
 * description: Display playlist detail
 */

import { connectSubject, createBlock, connectItemView } from '@metafox/framework';
import Base, { Props } from './Base';

const Enhance = connectSubject(connectItemView(Base));

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
