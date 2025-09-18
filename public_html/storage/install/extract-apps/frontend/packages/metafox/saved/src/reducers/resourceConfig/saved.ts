import { AppResource } from '@metafox/framework';

const initialState: AppResource = {
  actions: {
    searchItem: {
      pageUrl: '/saved/search',
      placeholder: 'Search saved'
    },
    viewAll: {
      apiUrl: '/saved',
      apiRules: {
        q: ['truthy', 'q'],
        sort: [
          'includes',
          'sort',
          ['latest', 'most_viewed', 'most_liked', 'most_discussed']
        ],
        when: ['includes', 'when', ['this_month', 'this_week', 'today']]
      }
    },
    deleteItem: {
      apiUrl: '/saveditems/unsave/?item_type=:item_type&item_id=:item_id',
      apiMethod: 'delete'
    },
    markAsOpened: {
      apiUrl: '/saveditems/open',
      apiMethod: 'put',
      apiParams: { saved_id: ':id', is_unopened: 0 }
    },
    markAsUnOpened: {
      apiUrl: '/saveditems/open',
      apiMethod: 'put',
      apiParams: { saved_id: ':id', is_unopened: 1 }
    }
  },
  menus: {
    addToCollection: {
      items: [
        {
          as: 'addToCollection',
          label: 'Add To Collection',
          icon: 'ico-add',
          testid: 'add to collection'
        }
      ]
    },
    itemActionMenu: {
      items: [
        {
          label: 'Share',
          icon: 'ico-share-o',
          testid: 'share',
          value: 'shareItem'
        },
        {
          label: 'Remove from Saved list',
          icon: 'ico-list-del',
          testid: 'unsave',
          value: 'deleteItem'
        },
        {
          label: 'Mark as opened',
          icon: 'ico-folder-check-o',
          testid: 'markAsOpened',
          value: 'saved/markAsOpened',
          showWhen: ['and', ['truthy', 'item.is_unopened']]
        },
        {
          label: 'Mark as unopened',
          icon: 'ico-folder-o',
          testid: 'markAsUnOpened',
          value: 'saved/markAsUnOpened',
          showWhen: ['and', ['falsy', 'item.is_unopened']]
        }
      ]
    }
  },
  forms: {}
};

export default initialState;
