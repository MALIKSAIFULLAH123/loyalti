/**
 * @type: block
 * name: video.block.videoListingBlock
 * title: Videos
 * keywords: video
 * description: Display video items.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const VideoListingBlock = createBlock<ListViewBlockProps>({
  name: 'VideoListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'video',
    dataSource: { apiUrl: '/video' }
  },
  defaults: {
    title: 'Videos',
    itemView: 'video.itemView.mainCard',
    gridContainerProps: { spacing: 2 },
    gridItemProps: { xs: 12, sm: 12, md: 6, lg: 6, xl: 6 },
    emptyPage: 'core.block.no_content'
  }
});

export default VideoListingBlock;
