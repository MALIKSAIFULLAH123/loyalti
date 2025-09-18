/**
 * @type: block
 * name: feed.block.groupProfileFeedDetail
 * title: Group's Feed
 * keywords: group
 * description: Display group's profile feed.
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const ProfileFeedDetailBlock = createBlock<ListViewBlockProps>({
  name: 'GroupProfileFeedListingBlock',
  extendBlock: 'core.block.listview',
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

export default ProfileFeedDetailBlock;
