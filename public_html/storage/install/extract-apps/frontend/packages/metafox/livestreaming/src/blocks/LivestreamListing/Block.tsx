/**
 * @type: block
 * name: livestreaming.block.listingBlock
 * title: Livestream
 * keywords: livestreaming
 * description: Display livestreaming listing.
 * chunkName: livestreaming
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const LivestreamListingBlock = createBlock<ListViewBlockProps>({
  name: 'LivestreamListingBlock',
  extendBlock: 'core.block.listview',
  defaults: {
    title: 'live_videos',
    itemView: 'live_video.itemView.mainCard',
    gridContainerProps: { spacing: 2 },
    gridLayout: 'Live_video - Main Card',
    emptyPage: 'core.block.no_content',
    itemLayout: 'Live_video - Main Card',
    dataSource: {
      apiUrl: '/live-video',
      apiParams: 'sort=most_viewed&view=all_streaming&limit=3'
    }
  }
});

export default LivestreamListingBlock;
