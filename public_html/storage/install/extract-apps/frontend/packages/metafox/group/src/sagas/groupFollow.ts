/**
 * @type: saga
 * name: saga.group.follow
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

export function* followGroup(action: ItemLocalAction) {
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

export function* unfollowGroup(action: ItemLocalAction) {
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
  takeEvery('group/follow', followGroup),
  takeEvery('group/unfollow', unfollowGroup)
];

export default sagas;
