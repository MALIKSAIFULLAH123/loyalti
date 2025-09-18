/**
 * @type: saga
 * name: saga.group.updateInfo
 */

import {
  ItemLocalAction,
  getGlobalContext,
  fulfillEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

export function* updateGroupsInfo(action: ItemLocalAction) {
  const { payload } = action;

  if (!payload) return;

  const { normalization } = yield* getGlobalContext();
  const result = normalization.normalize(payload);
  yield* fulfillEntity(result.data);
}

const sagas = [takeEvery('updateGroupInfo', updateGroupsInfo)];

export default sagas;
