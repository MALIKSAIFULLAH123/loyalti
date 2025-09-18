/**
 * @type: block
 * name: forum.block.mainListing
 * title: Main Listing Forum
 * keywords: forum
 * experiment: true
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

export default createBlock<ListViewBlockProps>({
  extendBlock: Base
});
