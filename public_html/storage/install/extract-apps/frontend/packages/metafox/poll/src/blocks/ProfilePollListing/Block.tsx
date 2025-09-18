/**
 * @type: block
 * name: poll.view.profilePollListing
 * title: Profile's Polls
 * keywords: poll, profile
 * description: Display profile's poll items.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const PollListingBlock = createBlock<ListViewBlockProps>({
  name: 'GroupPollListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    errorPage: 'default'
  },
  defaults: {
    title: 'polls',
    itemView: 'poll.itemView.profileCard',
    canLoadMore: true,
    dataSource: {
      apiUrl: '/poll',
      apiParams: 'user_id=:id&limit=6'
    },
    emptyPage: 'core.block.no_content',
    blockLayout: 'Profile - Contained',
    gridLayout: 'Poll - Main Card'
  }
});

export default PollListingBlock;
