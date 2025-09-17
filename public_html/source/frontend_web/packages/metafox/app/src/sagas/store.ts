/**
 * @type: saga
 * name: install
 */

import {
  getGlobalContext,
  getItem,
  handleActionError,
  handleActionFeedback,
  LocalAction,
  patchEntity
} from '@metafox/framework';
import { call, takeEvery } from 'redux-saga/effects';

function* onInstall(action: LocalAction<{ identity }>) {
  const { apiClient } = yield* getGlobalContext();

  const { identity } = action.payload;

  const item = yield* getItem(identity);

  try {
    yield* patchEntity(identity, {
      installation_status: 'installing'
    });
    const response = yield call(
      apiClient.post,
      '/admincp/app/store/product/install',
      {
        app_id: item.id,
        name: item.identity,
        app_version: item.version,
        release_channel: item.version_detail.release_channel
      }
    );

    yield* handleActionFeedback(response);
    const data = response?.data?.data || {};

    yield* patchEntity(
      identity,
      {
        ...data,
        installation_status: 'processing'
      },
      true
    );
  } catch (err) {
    yield* handleActionError(err);
    yield* patchEntity(identity, {
      installation_status: 'installed'
    });
  }
}

const sagas = [takeEvery('app/store/install', onInstall)];

export default sagas;
