/**
 * @type: block
 * name: livestreaming.block.listingProfileSidebarBlock
 * title: Livestream Profile Sidebar
 * keywords: livestreaming
 * description: Display livestreaming listing in profile sidebar.
 * experiment: true
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

// this block help client easy add block to sidebar without config, dont remove

const LivestreamListingBlock = createBlock<ListViewBlockProps>({
  name: 'LivestreamListingBlock',
  extendBlock: 'core.block.listview',
  defaults: {
    title: 'live_videos',
    itemView: 'live_video.itemView.mainCard',
    gridLayout: 'Livestream - Side Listing',
    emptyPage: 'core.block.no_content',
    itemLayout: 'Live_video - Main Card',
    blockLayout: 'Profile - Side Contained',
    dataSource: {
      apiUrl: '/live-video',
      apiParams: 'user_id=:id&sort=recent&limit=3'
    },
    headerActions: [
      {
        label: 'all',
        to: '/:module_name/:id/live-video'
      }
    ]
  }
});

export default LivestreamListingBlock;
