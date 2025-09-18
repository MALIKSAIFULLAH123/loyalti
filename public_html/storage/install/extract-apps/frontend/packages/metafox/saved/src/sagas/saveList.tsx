/**
 * @type: saga
 * name: save.saveList
 */

import {
  fulfillEntity,
  getGlobalContext,
  getResourceAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { APP_SAVED, RESOURCE_SAVED_LIST } from '../constant';

export function* fetchItemListMobile(action: {
  type: string;
  payload: { identity: string };
}) {
  const { apiClient, normalization, getPageParams } = yield* getGlobalContext();
  const pageParams: any = getPageParams();

  const config = yield* getResourceAction(
    APP_SAVED,
    RESOURCE_SAVED_LIST,
    'viewAll'
  );

  if (!pageParams?.collection_id || !config?.apiUrl) return;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod,
      url: `${config.apiUrl}/${pageParams.collection_id}`
    });

    const data = response?.data?.data;
    const result = normalization.normalize(data);

    yield* fulfillEntity(result.data);
  } catch (error) {}
}

const sagas = [takeEvery('savedList/fetchItemListMobile', fetchItemListMobile)];

export default sagas;
