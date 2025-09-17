/**
 * @type: block
 * name: friend.block.profileFriends
 * title: Profile Friends
 * keywords: friend, profile
 * description:
 * thumbnail:
 * experiment: true
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'ProfileFriends',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'friend',
    dataSource: {
      apiUrl: '/friend',
      apiParams: 'owner_id=:id&q=:q'
    }
  },
  defaults: {
    title: 'Friends',
    emptyPage: 'core.block.no_content',
    emptyPageProps: {
      title: 'no_friends_found'
    },
    canLoadMore: true,
    canLoadSmooth: true,
    itemView: 'friend.itemView.mainCard',
    blockLayout: 'Profile - Contained',
    gridLayout: 'Friend - Cards',
    itemLayout: 'Friend - Cards'
  }
});
