import { AppResource } from '@metafox/framework';
import { IS_NOT_SAVED, IS_SAVED } from '@metafox/saved/constant';
import filterEvents from './filterEvents';

const initialState: AppResource = {
  actions: {
    searchItem: {
      pageUrl: '/event/search',
      placeholder: 'Search event'
    },
    updateAvatar: {
      apiUrl: '/event/avatar/:id'
    },
    updateProfileCover: {
      apiUrl: 'event/cover/:id',
      apiMethod: 'post'
    },
    rsvp: {
      apiUrl: 'event/rsvp/:id',
      apiMethod: 'put'
    },
    viewAll: {
      apiUrl: '/event',
      apiRules: {
        q: ['truthy', 'q'],
        sort: [
          'includes',
          'sort',
          ['latest', 'most_viewed', 'most_liked', 'most_discussed']
        ],
        category: ['numeric', 'category'],
        when: [
          'includes',
          'when',
          ['ongoing', 'upcoming', 'this_month', 'this_week', 'today']
        ],
        view: [
          'includes',
          'view',
          [
            'feature',
            'my',
            'sponsor',
            'attending',
            'may-attend',
            'not-attending',
            'friend',
            'invites',
            'pending'
          ]
        ]
      }
    },
    viewItem: {
      apiUrl: '/event/:id',
      pageUrl: '/event/:id'
    },
    manageInvite: {
      pageUrl: '/event/manage/:id/invite',
      apiUrl: '/event-invite/form/:id'
    },
    deleteItem: {
      apiUrl: '/event/:id',
      confirm: {
        title: 'Confirm',
        message: 'Are you sure you want to delete this item permanently?'
      }
    },
    addItem: {
      pageUrl: '/event/add',
      apiUrl: '/event/form'
    },
    editItem: {
      pageUrl: '/event/manage/:id',
      apiUrl: '/event/form/:id'
    },
    sponsorItem: {
      apiUrl: '/event/sponsor/:id',
      apiMethod: 'put'
    },
    featureItem: {
      apiUrl: '/event/feature/:id',
      apiMethod: 'put'
    }
  },
  menus: {
    interestedMenu: {
      items: [
        {
          icon: 'ico-calendar-star',
          label: 'Interested',
          value: 'event/rsvp/interested, closeMenu',
          testid: 'interested'
        },
        {
          icon: 'ico-check-circle-o',
          label: 'Going',
          value: 'event/rsvp/going, closeMenu',
          testid: 'going',
          showWhen: ['neq', 'item.rsvp', 2]
        },
        {
          icon: 'ico-check-circle-o',
          label: 'Going',
          value: 'event/rsvp/going, closeMenu',
          testid: 'going',
          color: 'primary',
          showWhen: ['eq', 'item.rsvp', 2]
        },
        {
          icon: 'ico-calendar-star-o',
          label: 'Not Interested',
          value: 'event/rsvp/not_interested, closeMenu',
          testid: 'not_interested',
          showWhen: ['truthy', 'item.rsvp']
        }
      ]
    },
    itemActionMenu: {
      items: [
        {
          label: 'Edit Event',
          icon: 'ico-pencilline-o',
          value: 'closeMenu, editItem',
          testid: 'edit',
          showWhen: ['and', ['truthy', 'item.extra.can_edit']]
        },
        {
          label: 'Approve',
          icon: 'ico-pencilline-o',
          value: 'event/approveItem',
          testid: 'approve',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_approve'],
            ['falsy', 'item.is_pending']
          ]
        },
        {
          label: 'Invite people to come',
          icon: 'ico-user-man-plus',
          value: 'closeMenu, event/invitePeopleToCome',
          testid: 'invites',
          showWhen: ['and', ['truthy', 'item.extra.can_invite']]
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
          value: 'closeMenu, unsponsorItem',
          testid: 'remove_sponsor',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor'],
            ['truthy', 'item.is_sponsor']
          ]
        },
        {
          label: 'Feature',
          icon: 'ico-diamond',
          value: 'closeMenu, featureItem',
          testid: 'feature',
          showWhen: [
            'and',
            ['falsy', 'item.is_featured'],
            ['truthy', 'item.extra.can_feature']
          ]
        },
        {
          label: 'Un-Feature',
          icon: 'ico-diamond',
          testid: 'remove_feature',
          value: 'closeMenu, unfeatureItem',
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
    }
  },
  forms: {
    filter: filterEvents
  }
};

export default initialState;
