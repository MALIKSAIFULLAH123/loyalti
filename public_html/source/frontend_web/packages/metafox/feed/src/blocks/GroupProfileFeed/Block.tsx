/**
 * @type: block
 * name: feed.block.groupProfileFeeds
 * title: Group's Profile Feed
 * keywords: group
 * description: Display feed on the group's profile page.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

const ProfileFeedListingBlock = createBlock<ListViewBlockProps>({
  name: 'GroupProfileFeedListingBlock',
  extendBlock: Base,
  defaults: {
    title: 'Activities',
    canLoadMore: false,
    moduleName: 'feed',
    resourceName: 'feed',
    actionName: 'viewAll',
    blockLayout: 'Main Listings'
  },
  overrides: {
    showWhen: ['falsy', 'profile.is_pending']
  }
});

export default ProfileFeedListingBlock;
