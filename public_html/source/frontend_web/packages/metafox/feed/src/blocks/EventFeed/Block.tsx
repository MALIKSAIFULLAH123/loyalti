/**
 * @type: block
 * name: feed.block.eventProfileFeeds
 * title: Event's Feed
 * keywords: feed, profile, event
 * description: Display feed in event profile
 * thumbnail:
 * experiment: true
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

const ProfileEventFeedListingBlock = createBlock<ListViewBlockProps>({
  name: 'EventProfileFeedListingBlock',
  extendBlock: Base,
  defaults: {
    title: 'Event Activities',
    itemView: 'feed.itemView.mainCard',
    canLoadMore: true,
    canLoadSmooth: true,
    emptyPage: 'core.block.no_content_with_description',
    gridLayout: 'Feed - Main Card',
    itemLayout: 'Feed - Main Card',
    moduleName: 'feed',
    resourceName: 'feed',
    actionName: 'viewAll',
    blockLayout: 'Main Listings'
  }
});

export default ProfileEventFeedListingBlock;
