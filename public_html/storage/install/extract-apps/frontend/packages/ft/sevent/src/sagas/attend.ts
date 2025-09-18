/**
 * @type: saga
 * name: sevent.saga.attend
 */

import {
  getGlobalContext,
  getItem,
  handleActionError,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* attend({ payload }: ItemLocalAction) {
  const { identity, typeId } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient } = yield* getGlobalContext();

  try {
    yield apiClient.request({
      url: '/sevent/attend',
      method: 'get',
      params: `id=${item.id}&type_id=${typeId}`
    });

  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('sevent/attend', attend)];

export default sagas;
