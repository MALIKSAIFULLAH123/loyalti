/**
 * @type: block
 * name: music_song.block.songView
 * title: Song Detail
 * keywords: music
 * description: Display song detail
 */

import { connectSubject, createBlock } from '@metafox/framework';
import connectSongItem from '../../containers/connectSongItem';
import Base, { Props } from './Base';

const Enhance = connectSubject(connectSongItem(Base));

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    placeholder: 'Search'
  }
});
