/**
 * @type: block
 * name: video.block.BrowseVideos
 * title: Browse Videos
 * keywords: video
 * description: Display videos
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

export default createBlock<ListViewBlockProps>({
  extendBlock: Base,
  overrides: {
    contentType: 'video',
    itemProps: { showActionMenu: true }
  },
  defaults: {
    title: 'Videos',
    itemView: 'video.itemView.mainCard',
    blockLayout: 'Main Listings',
    gridLayout: 'Video - Main Card'
  }
});
