/**
 * @type: saga
 * name: comment.removePreviewLink
 */

import {
  getGlobalContext,
  getItem,
  handleActionError,
  getItemActionConfig,
  fulfillEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

export function* removePreviewLink(action) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  const { apiClient, compactUrl, normalization } = yield* getGlobalContext();

  try {
    const config = yield* getItemActionConfig(item, 'removePreviewItem');

    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });

    const data = response.data?.data;

    if (data) {
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('comment/removePreviewLink', removePreviewLink)];

export default sagas;
