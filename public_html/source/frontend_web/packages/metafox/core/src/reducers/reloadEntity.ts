/**
 * @type: saga
 * name: saga.reloadEntity
 */

import {
  fulfillEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* reloadEntity(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, compactUrl, normalization } = yield* getGlobalContext();

  if (!item) return;

  const config = yield* getItemActionConfig(item, 'viewItem');

  if (!config?.apiUrl) return;

  try {
    const response = yield apiClient.request({
      method: 'get',
      url: compactUrl(config.apiUrl, item)
    });
    const data = response?.data?.data;

    if (!data) return;

    const result = normalization.normalize(data);

    yield* fulfillEntity(result.data);
  } catch (error) {
    // silent or error
  }
}

const sagas = [takeEvery('@entity/reload', reloadEntity)];

export default sagas;
