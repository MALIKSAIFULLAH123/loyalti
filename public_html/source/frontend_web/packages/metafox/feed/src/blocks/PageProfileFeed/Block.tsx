/**
 * @type: block
 * name: feed.block.pageProfileFeeds
 * title: Page's Feed
 * keywords: feed
 * description: Display profile feeds
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

const ProfileFeedListingBlock = createBlock<ListViewBlockProps>({
  name: 'PageProfileFeedListingBlock',
  extendBlock: Base,
  overrides: {
    gridItemProps: { xs: 12, sm: 12, md: 12, lg: 12, xl: 12 },
    showWhen: ['falsy', 'profile.is_pending']
  },
  defaults: {
    title: 'Activities',
    blockProps: { variant: 'plained', noHeader: true },
    itemView: 'feed.itemView.mainCard',
    itemProps: {
      showActionMenu: true
    },
    gridContainerProps: { spacing: 2 },
    emptyPage: 'core.block.no_content_with_description',
    moduleName: 'feed',
    resourceName: 'feed',
    actionName: 'viewAll',
    blockLayout: 'Main Listings'
  }
});

export default ProfileFeedListingBlock;
