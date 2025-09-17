/**
 * @type: block
 * name: feed.block.homeFeeds
 * title: Home Feeds
 * keywords: feed
 * chunkName: feed
 * description: Display item feeds.
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

const HomeFeedListingBlock = createBlock<ListViewBlockProps>({
  name: 'HomeFeedListingBlock',
  extendBlock: Base,
  overrides: {
    contentType: 'feed',
    itemView: 'feed.itemView.mainCard',
    gridVariant: 'listView',
    gridItemProps: { xs: 12 },
    itemProps: { showActionMenu: true },
    emptyPage: 'core.block.no_content_with_description',
    authRequired: false
  },
  defaults: {
    isTrackingSponsor: true,
    title: 'Recent Activities',
    canLoadMore: true,
    canLoadSmooth: true,
    gridLayout: 'Feed - Main Card',
    itemLayout: 'Feed - Main Card',
    moduleName: 'feed',
    resourceName: 'feed',
    actionName: 'viewAll',
    showWhen: ['truthy', 'setting.activity']
  }
});

export default HomeFeedListingBlock;
