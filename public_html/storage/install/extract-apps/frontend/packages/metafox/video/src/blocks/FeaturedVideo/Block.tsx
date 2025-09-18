/**
 * @type: block
 * name: video.block.videoListingFeaturedBlock
 * title: Videos
 * keywords: video
 * description: Display video featured items.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const VideoListingFeaturedBlock = createBlock<ListViewBlockProps>({
  name: 'VideoListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'video',
    dataSource: { apiUrl: '/video', apiParams: 'view=friend&limit=4' }
  },
  defaults: {
    title: 'Videos Featured',
    blockProps: { variant: 'contained' },
    itemView: 'video.itemView.smallCard',
    gridContainerProps: { spacing: 2 },
    gridItemProps: { xs: 12, sm: 12, md: 12, lg: 12, xl: 12 }
  }
});

export default VideoListingFeaturedBlock;
