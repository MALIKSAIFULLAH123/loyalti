import { AppResource } from '@metafox/framework';
import { IS_NOT_SAVED, IS_SAVED } from '@metafox/saved/constant';
import filterForm from './filterFeed';

const initialState: AppResource = {
  name: 'Post',
  actions: {
    searchItem: {
      pageUrl: '/feed/search',
      placeholder: 'Search feeds'
    },
    viewAll: {
      apiUrl: '/feed',
      apiRules: {
        q: ['truthy', 'q'],
        hashtag: ['truthy', 'hashtag'],
        related_comment_friend_only: [
          'or',
          ['truthy', 'related_comment_friend_only'],
          ['falsy', 'related_comment_friend_only']
        ],
        sort: [
          'includes',
          'sort',
          ['latest', 'most_viewed', 'most_liked', 'most_discussed']
        ],
        from: ['includes', 'from', ['all', 'user', 'page', 'group']]
      }
    },
    viewItem: {
      apiUrl: '/feed/:id',
      pageUrl: '/feed/:id'
    },
    deleteItem: {
      apiUrl: '/feed/:id',
      confirm: {
        title: 'Confirm',
        message: 'Are you sure you want to delete this item permanently?'
      }
    },
    editItem: {
      apiUrl: '/feed/edit/:id',
      apiMethod: 'get'
    },
    hideItem: {
      apiUrl: '/feed/hide-feed/:id',
      apiMethod: 'post'
    },
    undoHideItem: {
      apiUrl: '/feed/hide-feed/:id',
      apiMethod: 'delete'
    },
    hideAll: {
      apiUrl: '/feed/hide-all/:id',
      apiMethod: 'post'
    },
    undoHideAll: {
      apiUrl: '/feed/hide-all/:id',
      apiMethod: 'delete'
    },
    snooze: {
      apiUrl: '/feed/snooze/:id',
      apiMethod: 'post'
    },
    undoSnooze: {
      apiUrl: '/feed/snooze/:id',
      apiMethod: 'delete'
    }
  },
  forms: {
    filter: filterForm
  },
  menus: {
    itemActionMenuForProfile: {
      items: [
        {
          label: 'Copy link',
          icon: 'ico-link',
          testid: 'copy_link',
          value: 'copyLink,closeMenu'
        },
        {
          label: 'Edit',
          icon: 'ico-pencilline-o',
          value: 'closeMenu, updateFeed',
          testid: 'edit',
          showWhen: ['and', ['truthy', 'item.extra.can_edit']]
        },
        {
          label: 'Report',
          icon: 'ico-warning-o',
          testid: 'report',
          value: 'closeMenu, reportItem',
          showWhen: ['and', ['truthy', 'item.extra.can_report']]
        },
        {
          icon: 'ico-sponsor',
          label: 'Sponsor in Feed',
          testid: 'sponsor_in_feed',
          value: 'closeMenu, sponsorItemInFeed',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor_in_feed'],
            ['falsy', 'item.is_sponsored_feed']
          ]
        },
        {
          icon: 'ico-sponsor',
          label: 'Unsponsor in Feed',
          testid: 'remove_sponsor_in_feed',
          value: 'closeMenu, unsponsorItemInFeed',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor_in_feed'],
            ['truthy', 'item.is_sponsored_feed']
          ]
        },
        {
          icon: 'ico-sponsor',
          label: 'Sponsor this item',
          value: 'closeMenu, sponsorItem',
          testid: 'sponsor',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor'],
            ['falsy', 'item.is_sponsor']
          ]
        },
        {
          icon: 'ico-sponsor',
          label: 'Unsponsor this item',
          testid: 'remove_sponsor',
          value: 'closeMenu, unsponsorItem',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor'],
            ['truthy', 'item.is_sponsor']
          ]
        },
        {
          label: 'Save',
          icon: 'ico-bookmark-o',
          value: 'closeMenu, saveItem',
          testid: 'save',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_save_item'],
            ['eq', 'item.is_saved', IS_NOT_SAVED]
          ]
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
          value: 'closeMenu, deleteItem',
          testid: 'delete',
          showWhen: ['and', ['truthy', 'item.extra.can_delete']],
          className: 'itemDelete'
        }
      ]
    },
    itemActionMenu: {
      items: [
        {
          label: 'Copy link',
          icon: 'ico-link',
          testid: 'copy_link',
          value: 'copyLink,closeMenu'
        },
        {
          label: 'Edit',
          icon: 'ico-pencilline-o',
          value: 'closeMenu, updateFeed',
          testid: 'edit',
          showWhen: ['and', ['truthy', 'item.extra.can_edit']]
        },
        {
          label: 'Hide this post',
          icon: 'ico-eye-off',
          testid: 'hide',
          value: 'closeMenu, hideItem',
          showWhen: ['truthy', 'item.extra.can_hide_item']
        },
        {
          label: 'Hide all from :user_full_name',
          icon: 'ico-eye-off',
          testid: 'hide_all_user',
          value: 'closeMenu, unfollowPoster',
          showWhen: ['truthy', 'item.extra.can_hide_all_user']
        },
        {
          label: 'Snooze :user_full_name for 30 days',
          icon: 'ico-clock-o',
          testid: 'snooze_user',
          value: 'closeMenu, snoozePoster',
          showWhen: ['truthy', 'item.extra.can_snooze_user']
        },
        {
          label: 'Snooze :owner_full_name for 30 days',
          icon: 'ico-clock-o',
          testid: 'snooze_owner',
          value: 'closeMenu, snoozeOwner',
          showWhen: ['truthy', 'item.extra.can_snooze_owner']
        },
        {
          label: 'Hide all from :owner_full_name',
          icon: 'ico-eye-off',
          testid: 'hide_all_parent',
          value: 'closeMenu, unfollowOwner',
          showWhen: ['truthy', 'item.extra.can_hide_all_owner']
        },
        {
          label: 'Report',
          icon: 'ico-warning-o',
          testid: 'report',
          value: 'closeMenu, reportItem',
          showWhen: ['and', ['truthy', 'item.extra.can_report']]
        },
        {
          icon: 'ico-sponsor',
          label: 'Sponsor in Feed',
          testid: 'sponsor_in_feed',
          value: 'closeMenu, sponsorItemInFeed',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor_in_feed'],
            ['falsy', 'item.is_sponsored_feed']
          ]
        },
        {
          icon: 'ico-sponsor',
          label: 'Unsponsor in Feed',
          testid: 'remove_sponsor_in_feed',
          value: 'closeMenu, unsponsorItemInFeed',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor_in_feed'],
            ['truthy', 'item.is_sponsored_feed']
          ]
        },
        {
          icon: 'ico-sponsor',
          label: 'Sponsor this item',
          value: 'closeMenu, sponsorItem',
          testid: 'sponsor',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor'],
            ['falsy', 'item.is_sponsor']
          ]
        },
        {
          icon: 'ico-sponsor',
          label: 'Unsponsor this item',
          testid: 'remove_sponsor',
          value: 'closeMenu, unsponsorItem',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor'],
            ['truthy', 'item.is_sponsor']
          ]
        },
        {
          label: 'Save',
          icon: 'ico-bookmark-o',
          value: 'closeMenu, saveItem',
          testid: 'save',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_save_item'],
            ['eq', 'item.is_saved', IS_NOT_SAVED]
          ]
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
          value: 'closeMenu, deleteItem',
          testid: 'delete',
          showWhen: ['and', ['truthy', 'item.extra.can_delete']],
          className: 'itemDelete'
        }
      ]
    }
  }
};

export default initialState;
