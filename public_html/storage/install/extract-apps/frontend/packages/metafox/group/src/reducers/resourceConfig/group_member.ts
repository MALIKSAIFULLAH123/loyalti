import { AppResource } from '@metafox/framework';
import {
  FRIENDSHIP_CAN_ADD_FRIEND,
  FRIENDSHIP_CONFIRM_AWAIT,
  FRIENDSHIP_IS_FRIEND,
  FRIENDSHIP_IS_OWNER,
  FRIENDSHIP_REQUEST_SENT
} from '@metafox/friend/constant';

const initialState: AppResource = {
  actions: {
    searchItem: {
      pageUrl: '/user/search',
      placeholder: 'Search user'
    },
    unblockItem: {
      apiUrl: '/account/blocked-user/:id',
      apiMethod: 'delete'
    },
    blockItem: {
      apiUrl: '/account/blocked-user',
      apiMethod: 'post',
      apiParams: {
        user_id: ':id'
      },
      confirm: {
        title: 'Are you sure?',
        message: 'Do you want to block this user?'
      }
    },
    viewAll: {
      apiUrl: '/user',
      apiRules: {
        q: ['truthy', 'q'],
        sort: [
          'includes',
          'sort',
          ['u.full_name', 'u.last_login', 'u.last_activity']
        ],
        gender: ['includes', 'gender', ['1', '2']]
      }
    },
    editItem: {
      pageUrl: '/user/profile',
      apiUrl: '/user/profile/form'
    },
    viewItem: {
      apiUrl: '/user/:id',
      pageUrl: '/user/:id'
    },
    deleteItem: {
      apiUrl: '/user/:id'
    },
    featureItem: {
      apiUrl: '/user/feature/:id'
    },
    updateAvatar: {
      apiUrl: '/user/avatar/:id'
    },
    updateProfileCover: {
      apiUrl: 'user/cover/:id',
      apiMethod: 'post'
    },
    removeProfileCover: {
      apiUrl: '/user/remove-cover',
      apiMethod: 'put',
      confirm: {
        message: 'Are you sure you want to delete this photo?'
      }
    },
    pokeItem: {
      apiUrl: 'user/poke',
      apiMethod: 'post',
      confirm: {
        title: 'Confirm',
        message: 'Do you want to send poke to this user?'
      }
    }
  },
  menus: {
    itemActionMenu: {
      items: [
        {
          label: 'Add Friend',
          icon: 'ico-user3-plus',
          value: 'user/addFriend',
          showWhen: ['eq', 'item.friendship', FRIENDSHIP_CAN_ADD_FRIEND],
          testid: 'addFriend'
        },
        {
          label: 'Cancel Request',
          icon: 'ico-user2-del-o',
          value: 'user/cancelRequest',
          showWhen: ['eq', 'item.friendship', FRIENDSHIP_REQUEST_SENT],
          testid: 'cancelRequest'
        },
        {
          icon: 'ico-pencilline-o',
          label: 'Edit List',
          value: 'closeMenu, friend/editList',
          showWhen: ['eq', 'item.friendship', FRIENDSHIP_IS_FRIEND],
          testid: 'editList'
        },
        {
          icon: 'ico-trash',
          label: 'Unfriend',
          value: 'user/unFriend',
          showWhen: ['eq', 'item.friendship', FRIENDSHIP_IS_FRIEND],
          testid: 'delete',
          className: 'itemDelete'
        },
        {
          label: 'Report',
          icon: 'ico-warning-o',
          value: 'closeMenu, reportItem',
          showWhen: ['and', ['truthy', 'item.extra.can_report']],
          testid: 'report'
        },
        {
          icon: 'ico-sponsor',
          label: 'Sponsor in Feed',
          value: 'closeMenu, sponsorItemInFeed',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor_in_feed'],
            ['falsy', 'item.is_sponsored_feed']
          ],
          testid: 'sponsorInFeed'
        },
        {
          icon: 'ico-sponsor',
          label: 'Unsponsor in Feed',
          value: 'closeMenu, unsponsorItemInFeed',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor_in_feed'],
            ['truthy', 'item.is_sponsored_feed']
          ],
          testid: 'deleteSponsorInFeed'
        },
        {
          icon: 'ico-sponsor',
          label: 'Sponsor this item',
          value: 'closeMenu, sponsorItem',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor'],
            ['falsy', 'item.is_sponsor']
          ],
          testid: 'sponsor'
        },
        {
          icon: 'ico-sponsor',
          label: 'Unsponsor this item',
          value: 'closeMenu, unsponsorItem',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor'],
            ['truthy', 'item.is_sponsor']
          ],
          testid: 'deleteSponsor'
        },
        {
          label: 'Feature',
          icon: 'ico-diamond',
          value: 'closeMenu, featureItem',
          showWhen: [
            'and',
            ['falsy', 'item.is_featured'],
            ['truthy', 'item.extra.can_feature']
          ],
          testid: 'feature'
        },
        {
          label: 'Un-Feature',
          icon: 'ico-diamond',
          value: 'closeMenu, unfeatureItem',
          showWhen: [
            'and',
            ['truthy', 'item.is_featured'],
            ['truthy', 'item.extra.can_feature']
          ],
          testid: 'deleteFeature'
        },
        {
          icon: 'ico-trash',
          label: 'Delete',
          value: 'closeMenu, deleteItem',
          showWhen: ['and', ['truthy', 'item.extra.can_delete']],
          testid: 'delete'
        }
      ]
    },
    profileMenu: {
      items: [
        {
          to: '/',
          label: 'Overview',
          active: true,
          tab: 'home',
          testid: 'home'
        },
        {
          to: '/about',
          label: 'About',
          tab: 'about',
          testid: 'about'
        },
        {
          to: '/friend',
          label: 'Friends',
          tab: 'friend',
          testid: 'friend'
        },
        {
          to: '/photo',
          label: 'Photos',
          tab: 'photo',
          testid: 'photo'
        },
        {
          to: '/video',
          label: 'Videos',
          tab: 'video',
          testid: 'video'
        },
        {
          to: '/groups',
          label: 'groups',
          tab: 'groups',
          testid: 'groups'
        },
        {
          to: '/listing',
          label: 'Listing',
          tab: 'marketplace',
          testid: 'marketplace'
        },
        {
          to: '/blog',
          label: 'Blogs',
          tab: 'blog',
          testid: 'blog'
        },
        {
          to: '/event',
          label: 'Events',
          tab: 'event',
          testid: 'event'
        }
      ]
    },
    profileActionMenu: {
      items: [
        {
          label: 'Un Friend',
          icon: 'ico-user3-minus-o',
          color: 'primary',
          value: 'user/unFriend, closeMenu',
          showWhen: ['and', ['eq', 'item.friendship', 1]],
          testid: 'unFriend'
        },
        {
          label: 'Add Friend',
          icon: 'ico-user3-plus',
          value: 'user/addFriend, closeMenu',
          showWhen: [
            'and',
            ['eq', 'item.friendship', FRIENDSHIP_CAN_ADD_FRIEND],
            ['truthy', 'item.extra.can_add_friend']
          ],
          testid: 'addFriend'
        },
        {
          label: 'Cancel Request',
          icon: 'ico-user2-del-o',
          value: 'user/cancelRequest, closeMenu',
          showWhen: [
            'and',
            ['eq', 'item.friendship', FRIENDSHIP_REQUEST_SENT],
            ['truthy', 'item.extra.can_add_friend']
          ],
          testid: 'cancelRequest'
        },
        {
          icon: 'ico-check',
          label: 'Confirm',
          color: 'primary',
          testid: 'confirmRequest',
          value: 'user/acceptFriendRequest, closeMenu',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_add_friend'],
            ['eq', 'item.friendship', FRIENDSHIP_CONFIRM_AWAIT]
          ]
        },
        {
          icon: 'ico-close',
          label: 'Delete',
          color: 'primary',
          testid: 'deleteRequest',
          value: 'user/denyFriendRequest, closeMenu',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_add_friend'],
            ['eq', 'item.friendship', FRIENDSHIP_CONFIRM_AWAIT]
          ]
        },
        {
          icon: 'ico-pencil',
          label: 'Edit Profile',
          color: 'primary',
          testid: 'editProfile',
          value: 'user/editProfile',
          showWhen: ['and', ['truthy', 'item.extra.can_edit']]
        },
        {
          icon: 'ico-comment-o',
          label: 'Message',
          color: 'primary',
          testid: 'message',
          value: 'chat/room/openChatRoom',
          showWhen: ['and', ['neq', 'item.friendship', FRIENDSHIP_IS_OWNER]]
        },
        {
          icon: 'ico-smile-o',
          label: 'Poke',
          color: 'primary',
          value: 'pokeItem',
          testid: 'poke',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_poke'],
            ['neq', 'item.friendship', FRIENDSHIP_IS_OWNER]
          ]
        },
        {
          icon: 'ico-diamond-o',
          label: 'Featured this user',
          color: 'primary',
          testid: 'feature',
          value: 'closeMenu, featureItem',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_feature'],
            ['falsy', 'item.is_featured']
          ]
        },
        {
          icon: 'ico-diamond-o',
          label: 'Un-Featured this user',
          color: 'primary',
          testid: 'unfeature',
          value: 'closeMenu, unfeatureItem',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_feature'],
            ['truthy', 'item.is_featured']
          ]
        },
        {
          icon: 'ico-ban',
          label: 'Block this user',
          color: 'primary',
          testid: 'block',
          value: 'blockItem',
          showWhen: [
            'and',
            ['falsy', 'item.is_blocked'],
            ['neq', 'item.friendship', FRIENDSHIP_IS_OWNER]
          ]
        },
        {
          icon: 'ico-ban',
          label: 'Un-Block this user',
          color: 'primary',
          testid: 'unblock',
          value: 'unblockItem',
          showWhen: [
            'and',
            ['truthy', 'item.is_blocked'],
            ['neq', 'item.friendship', FRIENDSHIP_IS_OWNER]
          ]
        },
        {
          icon: 'ico-warning-o',
          label: 'Report this user',
          color: 'primary',
          testid: 'report',
          value: 'closeMenu, reportItem',
          showWhen: ['and', ['neq', 'item.friendship', FRIENDSHIP_IS_OWNER]]
        },
        {
          icon: 'ico-gift-o',
          label: 'Gift Points',
          color: 'primary',
          testid: 'giftPoints',
          value: 'comingSoon',
          showWhen: ['and', ['neq', 'item.friendship', FRIENDSHIP_IS_OWNER]]
        }
      ]
    }
  },
  forms: {}
};

export default initialState;
