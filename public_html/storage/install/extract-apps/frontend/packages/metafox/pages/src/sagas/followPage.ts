/**
 * @type: saga
 * name: saga.page.follow
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
import { takeEvery } from 'redux-saga/effects';

export function* followPage(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  const config = yield* getItemActionConfig(item, 'follow');

  const { apiClient, compactUrl } = yield* getGlobalContext();

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, item)
    });
  
    yield* patchEntity(identity, response?.data?.data);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* unfollowPage(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);

  const config = yield* getItemActionConfig(item, 'unfollow');

  const { apiClient, compactUrl } = yield* getGlobalContext();

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });
    
    yield* patchEntity(identity, response?.data?.data);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('page/follow', followPage),
  takeEvery('page/unfollow', unfollowPage)
];

export default sagas;
