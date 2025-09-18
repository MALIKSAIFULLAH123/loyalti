import { AppResource } from '@metafox/framework';

const initialState: AppResource = {
  actions: {
    deleteItem: {
      confirm: {
        title: 'are_you_sure',
        message: 'Delete this message'
      }
    }
  },
  menus: {
    itemActionMenu: {
      items: [
        {
          label: 'reply',
          icon: 'ico-reply-o',
          value: 'closeMenu, chatplus/replyMessage',
          testid: 'replyMessage',
          showWhen: [
            'and',
            ['truthy', 'allowReply'],
            ['falsy', 'room.isBotRoom']
          ]
        },
        {
          label: 'quote',
          icon: 'ico-quotes-left',
          value: 'closeMenu, chatplus/quoteMessage',
          testid: 'quoteMessage',
          showWhen: [
            'and',
            ['truthy', 'allowQuote'],
            ['falsy', 'isSearch'],
            ['falsy', 'room.isBotRoom']
          ]
        },
        {
          label: 'copy',
          icon: 'ico-copy-o',
          value: 'closeMenu, chatplus/copyMessage',
          testid: 'copyMessage',
          showWhen: ['truthy', 'allowCopy']
        },
        {
          label: 'edit',
          icon: 'ico-pencilline-o',
          value: 'closeMenu, chatplus/editMessage',
          testid: 'editMessage',
          showWhen: [
            'and',
            ['truthy', 'allowEdit'],
            ['falsy', 'isSearch'],
            ['falsy', 'room.isBotRoom']
          ]
        },
        {
          label: 'unpin',
          icon: 'ico-magic',
          value: 'closeMenu, chatplus/unpinMessage',
          testid: 'unpinMessage',
          showWhen: [
            'and',
            ['truthy', 'allowPinning'],
            ['falsy', 'isSearch'],
            ['truthy', 'item.pinned']
          ]
        },
        {
          label: 'pin',
          icon: 'ico-magic',
          value: 'closeMenu, chatplus/pinMessage',
          testid: 'pinMessage',
          showWhen: [
            'and',
            ['truthy', 'allowPinning'],
            ['falsy', 'isSearch'],
            ['falsy', 'item.pinned']
          ]
        },
        {
          label: 'star',
          icon: 'ico-star-o',
          value: 'closeMenu, chatplus/starMessage',
          testid: 'starMessage',
          showWhen: [
            'and',
            ['truthy', 'allowStarring'],
            ['falsy', 'isSearch'],
            ['falsy', 'isStarred']
          ]
        },
        {
          label: 'remove_star',
          icon: 'ico-star-o',
          value: 'closeMenu, chatplus/unstarMessage',
          testid: 'unstarMessage',
          showWhen: [
            'and',
            ['truthy', 'allowStarring'],
            ['falsy', 'isSearch'],
            ['truthy', 'isStarred']
          ]
        },
        {
          as: 'divider',
          testid: 'divider',
          showWhen: [
            'and',
            ['truthy', 'canDelete'],
            ['falsy', 'isSearch'],
            ['falsy', 'room.isBotRoom']
          ]
        },
        {
          label: 'delete',
          icon: 'ico-trash-o',
          className: 'item-delete',
          value: 'closeMenu, chatplus/deleteMessage',
          testid: 'deleteMessage',
          showWhen: [
            'and',
            ['truthy', 'canDelete'],
            ['falsy', 'isSearch'],
            ['falsy', 'room.isBotRoom']
          ]
        }
      ]
    }
  }
};

export default initialState;
