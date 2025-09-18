/**
 * @type: block
 * name: music_song.block.songViewMini
 * title: Song Detail Mini
 * keywords: music
 * description: Display song detail mini
 */

import { connectSubject, createBlock } from '@metafox/framework';
import Base, { Props } from './Base';
import connectSongItem from '@metafox/music/containers/connectSongItem';

const Enhance = connectSubject(connectSongItem(Base));

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    placeholder: 'Search'
  }
});
