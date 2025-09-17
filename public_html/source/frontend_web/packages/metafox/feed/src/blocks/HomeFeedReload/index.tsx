/**
 * @type: block
 * name: feed.block.reloadFeed
 * title: Home Feeds Reload
 * keywords: feed
 * chunkName: feed
 * experiment: true
 */

import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Block';

const HomeFeedListingBlock = createBlock<ListViewBlockProps>({
  name: 'FeedReload',
  extendBlock: Base
});

export default HomeFeedListingBlock;
