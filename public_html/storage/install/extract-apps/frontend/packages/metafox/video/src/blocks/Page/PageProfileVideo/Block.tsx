/**
 * @type: block
 * name: video.block.pagesVideoListingBlock
 * title: Page's Videos
 * keywords: video
 * description: Display page's video items.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const VideoListingBlock = createBlock<ListViewBlockProps>({
  name: 'VideoListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'video',
    errorPage: 'default'
  },
  defaults: {
    title: 'Videos',
    itemView: 'video.itemView.mainCard',
    gridVariant: 'gridView'
  }
});

export default VideoListingBlock;
