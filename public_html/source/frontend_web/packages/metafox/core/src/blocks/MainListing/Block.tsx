/**
 * @type: block
 * name: core.block.mainListing
 * title: Main Listing
 * keywords: general
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

export default createBlock<ListViewBlockProps>({
  extendBlock: Base
});
