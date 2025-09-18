import { JUMP_MSG_ACTION } from '@metafox/chatplus/constants';

const state = {
  actions: {},
  menus: {
    itemActionMenu: {
      items: [
        {
          label: 'jump_to_message',
          icon: 'ico-external-link',
          value: JUMP_MSG_ACTION,
          testid: 'jumpMessage'
        },
        {
          label: 'unpin',
          icon: 'ico-magic',
          value: 'chatplus/unpinAndremoveMessage',
          testid: 'unpinMessage',
          showWhen: [
            'and',
            ['truthy', 'allowPinning'],
            ['truthy', 'item.pinned'],
            ['eq', 'type', 'pin']
          ]
        },
        {
          label: 'remove_star',
          icon: 'ico-star-o',
          value: 'chatplus/unstarAndremoveMessage',
          testid: 'unstarMessage',
          showWhen: [
            'and',
            ['truthy', 'allowStarring'],
            ['truthy', 'isStarred'],
            ['eq', 'type', 'star']
          ]
        },
        {
          label: 'delete',
          icon: 'ico-trash-o',
          className: 'item-delete',
          value: 'chatplus/deleteMessage',
          testid: 'deleteMessage',
          showWhen: ['and', ['truthy', 'canDelete']]
        }
      ]
    }
  }
};

export default state;
