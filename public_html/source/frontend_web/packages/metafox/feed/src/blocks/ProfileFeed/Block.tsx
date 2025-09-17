/**
 * @type: block
 * name: feed.block.profileFeeds
 * title: Profile Feeds
 * keywords: feed, profile
 * description:
 * thumbnail:
 * experiment: true
 */
import {
  connectItemView,
  connectSubject,
  createBlock,
  ListViewBlockProps
} from '@metafox/framework';
import Base from './Base';

const Enhance = connectSubject(connectItemView(Base, () => {}));

const ProfileFeedListingBlock = createBlock<ListViewBlockProps>({
  name: 'ProfileFeedListingBlock',
  extendBlock: Enhance,
  overrides: {
    gridVariant: 'listView',
    emptyPage: 'core.block.no_content_with_description'
  },
  defaults: {
    title: 'Activities',
    itemView: 'feed.itemView.mainCard',
    canLoadMore: true,
    canLoadSmooth: true,
    moduleName: 'feed',
    resourceName: 'feed',
    actionName: 'viewAll',
    blockLayout: 'Main Listings'
  }
});

export default ProfileFeedListingBlock;
