/**
 * @type: block
 * name: video.block.pagesVideoOverviewBlock
 * title: Page's Videos overview
 * keywords: video
 * description: Display page's video overview.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const VideoListingBlock = createBlock<ListViewBlockProps>({
  name: 'VideoOverviewBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'video',
    emptyPage: 'hide'
  },
  defaults: {
    title: 'Videos',
    blockProps: { variant: 'contained' },
    itemView: 'video.itemView.mainCard',
    gridContainerProps: { spacing: 2 },
    gridItemProps: { xs: 12, sm: 6, md: 4, lg: 4, xl: 4 },
    displayLimit: 1,
    gridVariant: 'gridView',
    dataSource: {
      apiUrl: '/video',
      apiParams: 'user_id=:id&limit=6'
    }
  }
});

export default VideoListingBlock;
