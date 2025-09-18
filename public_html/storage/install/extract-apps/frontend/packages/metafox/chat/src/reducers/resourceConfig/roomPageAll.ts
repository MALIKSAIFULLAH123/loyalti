import { AppResource } from '@metafox/framework';

const state: AppResource = {
  actions: {},
  menus: {
    itemActionMenu: {
      items: [
        {
          icon: 'ico-search-o',
          value: 'chat/room/toggleSearching',
          testid: 'toggleSearching',
          label: 'toggle_search',
          showWhen: ['and', ['truthy', 'isMobile']]
        },
        {
          label: 'delete',
          icon: 'ico-trash-o',
          value: 'chat/room/deleteRoom',
          testid: 'deleteRoom',
          showWhen: ['and', ['truthy', 'item.extra.can_delete']]
        },
        {
          label: 'more',
          icon: 'ico-dottedmore-vertical-o',
          testid: 'more',
          behavior: 'more'
        }
      ]
    }
  }
};

export default state;
