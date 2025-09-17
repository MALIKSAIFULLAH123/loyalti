/**
 * @type: saga
 * name: saga.user.manageHidden
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { compactData } from '@metafox/utils';
import { takeLatest } from 'redux-saga/effects';

function* unSnooze({ payload: { identity }, meta }: ItemLocalAction<any, any>) {
  const { apiClient } = yield* getGlobalContext();
  const item = yield* getItem(identity);
  const config = yield* getItemActionConfig(item, 'unSnooze');
  const user = yield* getItem(item.user);

  try {
    yield* patchEntity(identity, {
      extra: { can_unsnooze: false, can_snooze_forever: true }
    });

    const res = yield apiClient.request({
      method: config?.apiMethod,
      url: config.apiUrl,
      data: compactData(config?.apiParams, user)
    });

    yield* handleActionFeedback(res);
  } catch (error) {
    yield* patchEntity(identity, {
      extra: { can_unsnooze: true, can_snooze_forever: false }
    });
    yield* handleActionError(error);
  }
}

function* snoozeForever({
  payload: { identity },
  meta
}: ItemLocalAction<any, any>) {
  const { apiClient } = yield* getGlobalContext();
  const item = yield* getItem(identity);
  const config = yield* getItemActionConfig(item, 'snoozeForever');
  const user = yield* getItem(item.user);

  try {
    yield* patchEntity(identity, {
      extra: { can_unsnooze: true, can_snooze_forever: false }
    });
    const res = yield apiClient.request({
      method: config?.apiMethod,
      url: config.apiUrl,
      data: compactData(config?.apiParams, user)
    });
    yield* handleActionFeedback(res);
  } catch (error) {
    yield* patchEntity(identity, {
      extra: { can_unsnooze: false, can_snooze_forever: true }
    });
    yield* handleActionError(error);
  }
}

const sagas = [
  takeLatest('unSnooze', unSnooze),
  takeLatest('snoozeForever', snoozeForever)
];

export default sagas;
