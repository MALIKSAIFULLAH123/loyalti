/**
 * @type: saga
 * name: sevent.saga.free
 */

import {
  getGlobalContext,
  getItem,
  handleActionError,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* free({ payload }: ItemLocalAction) {
  const { identity, qty } = payload;
  const item = yield* getItem(identity);

  if (!item) return;
  
  const { apiClient } = yield* getGlobalContext();

  try {
    yield apiClient.request({
      url: '/sevent/free',
      method: 'get',
      params: `id=${item.id}&qty=${qty}`
    });

  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('sevent/free', free)];

export default sagas;
