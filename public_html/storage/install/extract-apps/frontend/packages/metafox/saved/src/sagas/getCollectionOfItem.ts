/**
 * @type: saga
 * name: saga.saveditems.getCollectionItem
 */

import { getGlobalContext, handleActionError } from '@metafox/framework';
import { RemoteDataSource } from '@metafox/framework/types';
import { takeLatest } from 'redux-saga/effects';

const cached: Record<string, any> = {};

function* getCommentCollections(action: {
  type: string;
  payload: {
    saved_id: number;
    limit: number;
    itemIdentity: string;
    forceReload: boolean;
    dataSource?: RemoteDataSource;
  };
  meta: { onSuccess: (data: any) => {} };
}) {
  const { limit, saved_id, itemIdentity, forceReload, dataSource } =
    action.payload;
  const { onSuccess } = action.meta;

  if (cached && cached[itemIdentity] && !forceReload) {
    typeof onSuccess === 'function' && onSuccess(cached[itemIdentity]);

    return;
  }

  const { apiClient, compactData } = yield* getGlobalContext();

  const config = dataSource;

  if (!config?.apiUrl || !config.apiParams) return;

  const params = compactData(config.apiParams, { saved_id, limit });

  try {
    const response = yield apiClient.request({
      url: config?.apiUrl,
      params
    });

    const data = response.data.data;
    const meta = response.data.meta;

    if (itemIdentity) {
      cached[itemIdentity] = { data, meta };
    }

    typeof onSuccess === 'function' && onSuccess({ data, meta });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeLatest('getCommentCollections', getCommentCollections)];

export default sagas;
