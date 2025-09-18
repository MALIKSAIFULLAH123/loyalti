/**
 * @type: saga
 * name: sevent.saga.favourite
 */

import {
  getGlobalContext,
  getItem,
  patchEntity,
  getItemActionConfig,
  handleActionError,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* favourite({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { compactUrl, apiClient } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'favourite');

  if (!config?.apiUrl) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config.apiMethod
    });

    if (response?.data) {
      yield* patchEntity(identity, {});
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('sevent/favourite', favourite)];

export default sagas;
