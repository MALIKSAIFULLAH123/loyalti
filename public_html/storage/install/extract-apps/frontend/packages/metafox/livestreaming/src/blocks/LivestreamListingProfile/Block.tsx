/**
 * @type: block
 * name: livestreaming.block.profileBlock
 * title: Livestream listing in profile page
 * keywords: livestreaming
 * description: Display livestream listing in profile page.
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const LivestreamListingProfileBlock = createBlock<ListViewBlockProps>({
  name: 'LivestreamListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    errorPage: 'default'
  },
  defaults: {
    title: 'live_videos',
    itemView: 'live_video.itemView.profileCard',
    gridLayout: 'Livestream - Flat View',
    emptyPage: 'core.block.no_content',
    itemLayout: 'Live_video - Main Card',
    dataSource: {
      apiUrl: '/live-video',
      apiParams: 'user_id=:id&sort=recent&limit=12'
    }
  }
});

export default LivestreamListingProfileBlock;
