/**
 * @type: block
 * name: feed.block.eventProfileFeed
 * title: Event's Profile Feed
 * keywords: event
 * thumbnail: 
 * description: Display event's profile feed.
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const ProfileFeedItemBlock = createBlock<ListViewBlockProps>({
  name: 'EventFeedItemBlock',
  extendBlock: 'core.block.listview',
  defaults: {
    canLoadMore: false, 
    moduleName: 'feed',
    resourceName: 'feed',
    actionName: 'viewOwnerItem',
    itemView: 'feed.itemView.mainCard',
    displayLimit: 1
  }
});

export default ProfileFeedItemBlock;
