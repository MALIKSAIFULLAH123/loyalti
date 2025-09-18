/**
 * @type: saga
 * name: saga.page.menu
 */
import {
  getGlobalContext,
  getResourceAction,
  handleActionError,
  handleActionFeedback
} from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';
import { APP_PAGE, RESOURCE_INTEGRATED } from '../constant';

export function* orderingItem(action: any) {
  const { apiClient, getPageParams, compactData } = yield* getGlobalContext();
  const { id } = getPageParams();
  const dataSource = yield* getResourceAction(
    APP_PAGE,
    RESOURCE_INTEGRATED,
    'orderingItem'
  );

  const { ids } = action.payload;
  const { onSuccess } = action.meta;

  if (!ids) return;

  try {
    const response = yield apiClient.request({
      url: dataSource.apiUrl,
      method: dataSource.apiMethod,
      data: compactData(dataSource.apiParams, { names: ids, id })
    });

    yield* handleActionFeedback(response);
    onSuccess && onSuccess();
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeLatest('page/orderingItem', orderingItem)];

export default sagas;
