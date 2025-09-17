import { LOGGED_OUT } from '@metafox/framework';
import produce, { Draft } from 'immer';
import { AppState } from '../types';

const initialState: AppState['status'] = {
  new_chat_message: 0,
  chat_message: 0,
  new_feed: 0,
  new_notification: 0,
  new_friend_request: 0
};

export default produce((draft: Draft<AppState['status']>, action) => {
  switch (action.type) {
    case 'core/status/decreaseRequest':
      if (draft.new_friend_request > 0) draft.new_friend_request--;

      break;
    case 'core/status/clearFriend':
      draft.new_friend_request = 0;
      break;
    case 'core/status/clearNotification':
      draft.new_notification = 0;
      break;
    case 'core/status/fulfill':
      draft = Object.assign(draft, action.payload);
      break;
    case LOGGED_OUT:
      // this is required to affected by immutable
      return draft;
  }
}, initialState);
