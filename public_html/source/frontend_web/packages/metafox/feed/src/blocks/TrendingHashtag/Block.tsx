/**
 * @type: block
 * name: core.block.trendingHashtag
 * title: Trending Hashtags
 * keywords: general
 * description: Display top trending hashtag
 */

import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  name: 'TrendingHashtagListing',
  overrides: {
    contentType: 'hashtag'
  },
  defaults: {
    title: 'Blogs',
    itemView: 'feed.itemView.trendingHashtag',
    blockLayout: 'Main Listings',
    gridLayout: 'Shortcut - Small Lists',
    moduleName: 'search',
    resourceName: 'search',
    actionName: 'hashtagTrending'
  }
});
