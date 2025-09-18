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
          label: 'toggle_search'
        },
        {
          icon: 'ico-comment-square-dots-o',
          value: 'chat/room/openInMessenger',
          testid: 'openInMessenger',
          label: 'open_in_messenger'
        },

        {
          label: 'delete',
          icon: 'ico-trash-o',
          value: 'closeMenu, chat/room/deleteRoom',
          testid: 'deleteRoom',
          showWhen: ['and', ['truthy', 'item.extra.can_delete']]
        },

        {
          label: 'more',
          icon: 'ico-dottedmore-vertical-o',
          testid: 'more',
          behavior: 'more'
        },
        {
          icon: 'ico-minus',
          value: 'closeMenu, chat/openRooms/minimize',
          testid: 'minimize',
          label: 'minimize',
          behavior: 'close'
        },
        {
          label: 'close',
          icon: 'ico-close',
          value: 'closeMenu, chat/closePanel',
          testid: 'closePanel',
          behavior: 'close'
        }
      ]
    }
  }
};

export default state;
