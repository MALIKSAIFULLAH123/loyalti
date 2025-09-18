import { AppResource } from '@metafox/framework';
import { IS_NOT_SAVED, IS_SAVED } from '@metafox/saved/constant';
import filterAlbums from './filterAlbums';

const initialState: AppResource = {
  actions: {
    searchItem: {
      placeholder: 'Search albums',
      pageUrl: '/music/album/search'
    },
    viewAll: {
      apiUrl: '/music-album/',
      apiRules: {
        q: ['truthy', 'q'],
        sort: [
          'includes',
          'sort',
          ['latest', 'most_viewed', 'most_liked', 'most_discussed']
        ],
        genre: ['numeric', 'genre'],
        when: ['includes', 'when', ['this_month', 'this_week', 'today']],
        view: ['includes', 'view', ['my', 'friend', 'pending']]
      }
    },
    viewItem: {
      pageUrl: '/music/album/:id',
      apiUrl: '/music-album/:id'
    },
    deleteItem: {
      apiUrl: '/music-album/:id',
      confirm: {
        title: 'Confirm',
        message: 'Are you sure you want to delete this item permanently?'
      }
    },
    addItem: {
      pageUrl: '/music-album/add',
      apiUrl: '/music-album/form'
    },
    editItem: {
      pageUrl: '/music-album/edit/:id',
      apiUrl: '/music-album/form/:id'
    },
    sponsorItem: {
      apiUrl: '/music-album/sponsor/:id'
    },
    featureItem: {
      apiUrl: '/music-album/feature/:id'
    }
  },
  menus: {
    itemActionMenu: {
      items: [
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
          label: 'Sponsor in Feed',
          value: 'closeMenu, sponsorItemInFeed',
          testid: 'sponsor_in_feed',
          showWhen: [
            'and',
            ['truthy', 'item.extra.can_sponsor_in_feed'],
            ['falsy', 'item.is_sponsored_feed']
          ]
        },
        {
          icon: 'ico-sponsor',
          label: 'Unsponsor in Feed',
          testid: 'unsponsor in feed',
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
          value: 'closeMenu, unfeatureItem',
          testid: 'unsponsor',
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
    filter: filterAlbums
  }
};

export default initialState;
