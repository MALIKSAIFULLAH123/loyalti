import produce, { Draft } from 'immer';
import { AppState } from '../types';

export default produce(
  (draft: Draft<AppState['notifications']>, action) => {
    switch (action.type) {
      case 'chat/notifications/updateUnread': {
        const { total_notification = 0 } = action.payload || {};
        draft.total_notification = total_notification;
        break;
      }
    }
  },
  {
    total_notification: 0
  }
);
