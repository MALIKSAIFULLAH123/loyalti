import { AppState } from '@metafox/chatplus/types';
import produce, { Draft } from 'immer';

export default produce(
  (draft: Draft<AppState['notifications']>, action) => {
    switch (action.type) {
      case 'chatplus/init': {
        Object.assign(draft, action.payload?.notifications);
        break;
      }

      case 'chatplus/notifications/updateUnread': {
        const { unread } = action.payload;
        draft.unread = unread;
        break;
      }
    }
  },
  {
    unread: 0
  }
);
