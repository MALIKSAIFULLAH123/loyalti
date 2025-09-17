/**
 * @type: block
 * name: friend.block.friendBirthdaySubside
 * title: Friends Birthday Subside
 * keywords: friend
 * description:
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const FriendListingBlock = createBlock<ListViewBlockProps>({
  name: 'FriendBirthdayListingSubside',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'friend'
  },
  defaults: {
    moduleName: 'friend',
    resourceName: 'friend',
    actionName: 'upcomingFriendBirthday',
    title: 'birthdays',
    canLoadMore: false,
    blockId: 's8f2c',
    numberOfItemsPerPage: 4,
    emptyPage: 'hide',
    itemView: 'friend.itemView.birthdaySmallCard',
    contentType: 'friend',
    canLoadSmooth: false,
    blockLayout: 'Profile - Side Contained (no gutter)',
    gridLayout: 'Friend List - Small List',
    noContentLayout: 'No Content With Icon & Note',
    itemLayout: 'Friend - Small Cards',
    pagingId: 'friend/birthdayInHomepage'
  }
});

export default FriendListingBlock;
