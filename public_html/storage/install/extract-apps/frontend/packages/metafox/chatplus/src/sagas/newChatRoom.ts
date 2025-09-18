/**
 * @type: saga
 * name: chatplus.newChatRoom
 */

import { getGlobalContext, LocalAction } from '@metafox/framework';
import { put, takeEvery, debounce } from 'redux-saga/effects';
import { SuggestionItemShape } from '../types';
import handleActionErrorChat from './handleActionErrorChat';
import { addRoomToChatDock } from './helpers';
import { isFunction } from 'lodash';

function* search(action: LocalAction<{ query: string; excludes: boolean }>) {
  const { chatplus } = yield* getGlobalContext();
  const { query, excludes = [] } = action.payload;

  try {
    const data = yield chatplus.waitDdpMethod({
      name: 'chatplus/spotlight',
      params: [query, excludes, { rooms: false, users: true }]
    });

    yield put({
      type: 'chatplus/newChatRoom/search/FULFILL',
      payload: data
    });
  } catch (err) {
    // do nothing
  }
}

function* submit(
  action: LocalAction<{
    users: SuggestionItemShape[];
    pageMessage?: boolean;
  }> & {
    meta: {
      onFailure?: () => void;
      onSuccess?: () => void;
    };
  }
) {
  const { chatplus } = yield* getGlobalContext();
  const { users = [] } = action.payload;
  const { onSuccess, onFailure } = action?.meta || {};

  try {
    const result = yield chatplus.createNewMessage(users);

    if (!result) return;

    const rid = result?.room?.id || result?.room?._id || result?.rid;

    if (!action.payload?.pageMessage) {
      yield put({
        type: 'chatplus/closePanel',
        payload: { identity: 'NEW_CHAT_ROOM' }
      });

      yield* addRoomToChatDock(rid);
    }

    isFunction(onSuccess) && onSuccess();
  } catch (error) {
    isFunction(onFailure) && onFailure();
    yield* handleActionErrorChat(error);
  }
}

const sagas = [
  debounce(500, 'chatplus/newChatRoom/submit', submit),
  takeEvery('chatplus/newChatRoom/search', search)
];

export default sagas;
