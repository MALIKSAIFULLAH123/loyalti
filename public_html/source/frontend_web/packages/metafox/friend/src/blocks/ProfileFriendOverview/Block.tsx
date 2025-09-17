/**
 * @type: block
 * name: friend.block.profileFriendOverview
 * title: Profile Friends
 * keywords: friend, profile
 * description: Display friend profile.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const ProfileFriendOverviewBlock = createBlock<ListViewBlockProps>({
  name: 'ProfileFriendOverview',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'friend',
    dataSource: {
      apiUrl: '/friend',
      apiParams: 'user_id=:id&view=profile&limit=6'
    },
    headerActions: [
      {
        label: 'all',
        to: '/user/:profile_id/friend'
      }
    ],
    showWhen: [
      'and',
      ['truthy', 'profile.profile_settings.friend_view_friend'],
      ['truthy', 'profile.profile_settings.profile_view_profile']
    ]
  },
  defaults: {
    title: 'Friend',
    itemView: 'friend.itemView.smallCard'
  }
});

export default ProfileFriendOverviewBlock;
