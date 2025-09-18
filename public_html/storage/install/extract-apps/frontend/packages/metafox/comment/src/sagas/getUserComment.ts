/**
 * @type: saga
 * name: comment.getUserComment
 */

import {
  getGlobalContext,
  handleActionError,
  getResourceAction
} from '@metafox/framework';
import { RemoteDataSource } from '@metafox/framework/types';
import { takeLatest } from 'redux-saga/effects';

const cached: Record<string, any> = {};

function* getCommentListUser(action: {
  type: string;
  payload: {
    item_type: string;
    item_id: string;
    limit: number;
    resource_name: string;
    id: number;
    itemIdentity: string;
    forceReload: boolean;
    dataSource?: RemoteDataSource;
  };
  meta: { onSuccess: (data: any) => {} };
}) {
  const {
    item_type,
    item_id,
    limit,
    resource_name,
    id,
    itemIdentity,
    forceReload,
    dataSource
  } = action.payload;
  const { onSuccess } = action.meta;

  if (cached && cached[itemIdentity] && !forceReload) {
    typeof onSuccess === 'function' && onSuccess(cached[itemIdentity]);

    return;
  }

  const { apiClient, compactData } = yield* getGlobalContext();

  let config = dataSource;

  if (!config) {
    config = yield* getResourceAction(
      'comment',
      'comment',
      'getUsersCommentByItem'
    );
  }

  if (!config?.apiUrl) return;

  const sendData = {
    item_id: item_id || id,
    item_type: item_type || resource_name,
    limit
  };

  const params = compactData(config.apiParams, sendData);

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

const sagas = [takeLatest('getCommentListUser', getCommentListUser)];

export default sagas;
