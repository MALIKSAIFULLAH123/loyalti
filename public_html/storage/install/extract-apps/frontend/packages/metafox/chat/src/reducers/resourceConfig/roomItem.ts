const state = {
  actions: {},
  menus: {
    itemActionMenu: {
      items: [
        {
          label: 'delete',
          icon: 'ico-trash-o',
          value: 'closeMenu, chat/room/deleteRoom',
          testid: 'deleteRoom',
          showWhen: ['and', ['truthy', 'item.extra.can_delete']]
        }
      ]
    }
  }
};

export default state;
