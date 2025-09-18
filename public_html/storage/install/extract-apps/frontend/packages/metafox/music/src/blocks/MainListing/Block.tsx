/**
 * @type: block
 * name: music.block.mainListing
 * title: Main Listing Music
 * keywords: music
 * experiment: true
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

export default createBlock<ListViewBlockProps>({
  extendBlock: Base
});
