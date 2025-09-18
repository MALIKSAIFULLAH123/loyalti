/**
 * @type: saga
 * name: music.saga.fetchItem
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  patchEntity,
  fulfillEntity
} from '@metafox/framework';
import { uniq } from 'lodash';
import { takeEvery } from 'redux-saga/effects';

function* getListSong(action: {
  type: string;
  payload: { identity: string };
  meta: { onSuccess?: (data: any) => {} };
}) {
  const { identity } = action.payload;
  const numberOfItemsPerPage = 20;

  const { onSuccess } = action.meta;
  const item = yield* getItem(identity);

  if (!item) return;

  const { compactUrl, apiClient, normalization } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'getAlbumItems');

  if (!config?.apiUrl) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      params: { limit: numberOfItemsPerPage, page: 1 },
      method: config.apiMethod
    });
    const data = response?.data?.data;
    onSuccess({ data });
    const result = normalization.normalize(data);
    yield* fulfillEntity(result.data);

    yield* patchEntity(identity, {
      songs: result?.ids
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* loadMoreListSong(action: {
  type: string;
  payload: { identity: string };
  meta: { onSuccess?: () => {} };
}) {
  const { identity } = action.payload;
  const numberOfItemsPerPage = 20;

  const { onSuccess } = action.meta;
  const item = yield* getItem(identity);

  if (!item) return;

  const { compactUrl, apiClient, normalization } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'getAlbumItems');

  if (!config?.apiUrl) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      params: {
        page: Math.round(item.songs.length / numberOfItemsPerPage) + 1,
        limit: numberOfItemsPerPage
      },
      method: config.apiMethod
    });
    const data = response?.data?.data;
    
    const result = normalization.normalize(data);
    yield* fulfillEntity(result.data);

    yield* patchEntity(identity, {
      songs: uniq(item.songs.concat(result?.ids))
    });
    onSuccess();

  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('music/getListSong', getListSong),
  takeEvery('music/loadMoreListSong', loadMoreListSong)
];

export default sagas;
