import { AppResource } from '@metafox/framework';
import {
  MEMBERSHIP,
  MEMBERSHIP_CONFIRM_AWAIT,
  NOT_MEMBERSHIP
} from '@metafox/group/constant';
import { IS_NOT_SAVED, IS_SAVED } from '@metafox/saved/constant';
import filterGroups from './filterGroups';
import filterInGroup from './filterInGroup';

const initialState: AppResource = {
  actions: {
    joinGroup: {
      apiUrl: '/group-member',
      apiMethod: 'post'
    },
    unjoinGroup: {
      apiUrl: '/group-member/:id',
      apiMethod: 'delete',
      confirm: {
        title: 'Confirm',
        message: 'Are you sure you want to leave this group?'
      }
    },
    cancelRequest: {
      apiUrl: '/group-member/request/:id',
      apiMethod: 'delete',
      confirm: {
        title: 'Confirm',
        message: 'Are you sure you want to cancel this request?'
      }
    },
    searchItem: {
      pageUrl: '/group/search',
      placeholder: 'Search group'
    },
    updateAvatar: {
      apiUrl: '/group/avatar/:id'
    },
    updateProfileCover: {
      apiUrl: 'group/cover/:id',
      apiMethod: 'post'
    },
    removeProfileCover: {
      apiUrl: '/group/cover/:id',
      apiMethod: 'delete',
      confirm: {
        message: 'Are you sure you want to delete this photo?'
      }
    },
    viewAll: {
      apiUrl: '/group',
      apiRules: {
        q: ['truthy', 'q'],
        sort: [
          'includes',
          'sort',
          ['latest', 'most_viewed', 'most_member', 'most_discussed']
        ],
        category_id: ['truthy', 'category_id'],
        type_id: ['truthy', 'type_id'],
        when: ['includes', 'when', ['this_month', 'this_week', 'today']],
        view: ['includes', 'view', ['my', 'friend', 'invited', 'joined']]
      }
    },
    viewSearchInGroup: {
      apiUrl: 'feed/?item_type=group&item_id=:id',
      apiRules: {
        q: ['truthy', 'q'],
        sort: [
          'includes',
          'sort',
          ['latest', 'most_viewed', 'most_member', 'most_discussed']
        ],
        when: ['includes', 'when', ['this_month', 'this_week', 'today']],
        view: ['includes', 'view', ['my', 'friend', 'invited', 'joined']],
        related_comment_friend_only: [
          'or',
          ['truthy', 'related_comment_friend_only'],
          ['falsy', 'related_comment_friend_only']
        ]
      }
    },
    viewItem: {
      apiUrl: '/group/:id',
      pageUrl: '/group/:id'
    },
    deleteItem: {
      apiUrl: '/group/:id',
      confirm: {
        title: 'Confirm',
        message: 'Are you sure you want to delete this item permanently?'
      }
    },
    addItem: {
      pageUrl: '/group/add',
      apiUrl: '/group/form'
    },
    editItem: {
      pageUrl: '/group/settings/:id',
      apiUrl: '',
      apiMethod: 'get'
    },
    sponsorItem: {
      apiUrl: '/group/sponsor/:id',
      apiMethod: 'patch'
    },
    featureItem: {
      apiUrl: '/group/feature/:id',
      apiMethod: 'patch'
    }
  },
  menus: {
    profilePopoverMenu: {
      items: [
        {
          label: 'Cancel Request',
          icon: 'ico-close',
          value: 'closeMenu, group/cancelRequest',
          testid: 'edit',
          showWhen: ['eq', 'item.membership', MEMBERSHIP_CONFIRM_AWAIT]
        },
        {
          label: 'Leave Group',
          icon: 'ico-close',
          value: 'closeMenu, group/unjoin',
          testid: 'edit',
          showWhen: ['eq', 'item.membership', MEMBERSHIP]
        },
        {
          label: 'Invite Members',
          icon: 'ico-user-man-plus-o',
          value: 'closeMenu, comingSoon',
          testid: 'report',
          showWhen: ['eq', 'item.membership', MEMBERSHIP]
        },
        {
          label: 'Report',
          icon: 'ico-warning-o',
          value: 'closeMenu, reportItem',
          testid: 'report',
          showWhen: ['and', ['truthy', 'item.extra.can_report']]
        }
      ]
    },
    itemActionMenu: {
      items: [
        {
          label: 'Cancel Request',
          icon: 'ico-close',
          value: 'closeMenu, group/cancelRequest',
          testid: 'cancelRequest',
          showWhen: ['eq', 'item.membership', MEMBERSHIP_CONFIRM_AWAIT]
        },
        {
          label: 'Edit',
          icon: 'ico-pencilline-o',
          value: 'closeMenu, editItem',
          testid: 'edit',
          showWhen: ['and', ['truthy', 'item.extra.can_edit']]
        },
        {
          label: 'Report',
          icon: 'ico-warning-o',
          value: 'closeMenu, reportItem',
          testid: 'report',
          showWhen: ['and', ['truthy', 'item.extra.can_report']]
        },
        {
          icon: 'ico-sponsor',
          label: 'Sponsor',
          value: 'closeMenu, sponsorItem',
          testid: 'sponsor',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor'],
            ['falsy', 'item.is_sponsor']
          ]
        },
        {
          icon: 'ico-envelope-o',
          label: 'Invite Friends',
          value: 'closeMenu, group/inviteFriends',
          testid: 'invite'
        },
        {
          icon: 'ico-sponsor',
          label: 'Unsponsor',
          value: 'closeMenu, unsponsorItem',
          testid: 'unsponsor',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor'],
            ['truthy', 'item.is_sponsor']
          ]
        },
        {
          label: 'Feature',
          icon: 'ico-diamond',
          testid: 'feature',
          value: 'closeMenu, featureItem',
          showWhen: [
            'and',
            ['falsy', 'item.is_featured'],
            ['truthy', 'item.extra.can_feature']
          ]
        },
        {
          label: 'Un-Feature',
          icon: 'ico-diamond',
          value: 'closeMenu, unfeatureItem',
          testid: 'unfeature',
          showWhen: [
            'and',
            ['truthy', 'item.is_featured'],
            ['truthy', 'item.extra.can_feature']
          ]
        },
        {
          testid: 'save',
          label: 'Save',
          icon: 'ico-save-o',
          value: 'closeMenu, saveItem',
          showWhen: ['and', ['eq', 'item.is_saved', IS_NOT_SAVED]]
        },
        {
          testid: 'un-save',
          label: 'Remove from Saved list',
          icon: 'ico-list-del',
          value: 'closeMenu, undoSaveItem',
          showWhen: ['and', ['eq', 'item.is_saved', IS_SAVED]]
        },
        {
          icon: 'ico-trash',
          label: 'Delete',
          testid: 'delete',
          value: 'closeMenu, deleteItem',
          showWhen: ['and', ['truthy', 'item.extra.can_delete']]
        }
      ]
    },
    profileMenu: {
      items: [
        {
          to: '/',
          label: 'Discussions',
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
          to: '/topic',
          label: 'Topics',
          tab: 'topic',
          testid: 'topic'
        },
        {
          to: '/member',
          label: 'Members',
          tab: 'member',
          testid: 'member'
        },
        {
          to: '/media',
          label: 'Media Files',
          tab: 'media',
          testid: 'media'
        },
        {
          to: '/poll',
          label: 'polls',
          tab: 'poll',
          testid: 'poll'
        },
        {
          to: '/quiz',
          label: 'quizzes',
          tab: 'quiz',
          testid: 'quiz'
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
          icon: 'ico-check',
          label: 'Joined',
          testid: 'joined',
          variant: 'outlined',
          disabled: true,
          showWhen: ['eq', 'item.membership', MEMBERSHIP]
        },
        {
          icon: 'ico-plus',
          label: 'Request to Join',
          testid: 'join',
          variant: 'contained',
          value: 'closeMenu, group/join',
          disabled: false,
          showWhen: ['eq', 'item.membership', NOT_MEMBERSHIP]
        },
        {
          icon: 'ico-clock-o',
          label: 'Pending Request',
          variant: 'outlined',
          disabled: true,
          testid: 'pending',
          showWhen: ['eq', 'item.membership', MEMBERSHIP_CONFIRM_AWAIT]
        },
        {
          label: 'Cancel Request',
          icon: 'ico-close',
          value: 'closeMenu, group/cancelRequest',
          testid: 'cancelRequest',
          showWhen: ['eq', 'item.membership', MEMBERSHIP_CONFIRM_AWAIT]
        },
        {
          label: 'Leave group',
          icon: 'ico-user3-next-o',
          value: 'closeMenu, group/unjoin',
          testid: 'leaveGroup',
          showWhen: [
            'and',
            ['falsy', 'item.is_admin'],
            ['eq', 'item.membership', MEMBERSHIP]
          ]
        },
        {
          icon: 'ico-gear-o',
          label: 'Manage',
          value: 'closeMenu, group/manage',
          testid: 'manage',
          showWhen: ['truthy', 'item.extra.can_edit']
        },
        {
          icon: 'ico-diamond-o',
          label: 'Featured',
          color: 'primary',
          testid: 'feature',
          showWhen: ['truthy', 'item.extra.can_feature']
        },
        {
          icon: 'ico-sponsor',
          label: 'Sponsor',
          color: 'primary',
          testid: 'sponsor',
          showWhen: ['truthy', 'item.extra.can_sponsor']
        },
        {
          icon: 'ico-user2-next-o',
          label: 'Reassign Owner',
          color: 'primary',
          testid: 'reassign owner',
          showWhen: ['truthy', 'item.is_admin']
        },
        {
          icon: 'ico-warning-o',
          label: 'Report group',
          color: 'primary',
          testid: 'report',
          showWhen: ['truthy', 'item.extra.can_report']
        }
      ]
    }
  },
  forms: {
    filter: filterGroups,
    filterInProfile: filterInGroup
  }
};
export default initialState;
