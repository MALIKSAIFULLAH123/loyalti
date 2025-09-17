import produce from 'immer';
import { AppState } from '../types';

const data: AppState['shareOptions'] = [
  {
    icon: 'ico-share-alt-o',
    label: 'share_now',
    value: 'closeMenu,shareNow',
    testid: 'share_now'
  },
  {
    icon: 'ico-compose',
    label: 'share_to_newsfeed',
    value: 'closeMenu,shareToNewsFeed',
    testid: 'share_to_newsfeed'
  },
  {
    icon: 'ico-user1-two',
    label: 'share_on_friends',
    value: 'shareOnFriendProfile',
    testid: 'share_on_friends'
  },
  {
    icon: 'ico-flag-waving',
    label: 'share_on_page',
    value: 'shareOnPageProfile',
    testid: 'share_on_page'
  },
  {
    icon: 'ico-user3-three',
    label: 'share_on_group',
    value: 'shareOnGroupProfile',
    testid: 'share_on_group'
  },
  {
    label: 'copy_link',
    icon: 'ico-link',
    testid: 'copy_link',
    value: 'copyLink'
  }
];

export default produce((draft, action) => {}, data);
