/**
 * @type: saga
 * name: settings.reviewPosts
 */
import { APP_FEED } from '@metafox/feed';
import {
  deleteEntity,
  getGlobalContext,
  getItemActionConfig,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction
} from '@metafox/framework';
import { compactUrl } from '@metafox/utils';
import { takeEvery, takeLatest } from 'redux-saga/effects';

export function* reviewPostsAllowed(action: ItemLocalAction) {
  const { apiClient } = yield* getGlobalContext();
  const { identity } = action.payload;

  if (!identity) return;

  const id = identity.split('.')[3];

  const config = yield* getItemActionConfig(
    { resource_name: APP_FEED, module_name: APP_FEED },
    'allowed'
  );

  if (!config) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, { id }),
      method: config.apiMethod,
      params: config.apiParams
    });

    yield* deleteEntity(identity);

    yield* handleActionFeedback(response);
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* reviewPostsHideItem(action: ItemLocalAction) {
  const { apiClient } = yield* getGlobalContext();
  const { identity } = action.payload;

  if (!identity) return;

  const id = identity.split('.')[3];

  const config = yield* getItemActionConfig(
    { resource_name: APP_FEED, module_name: APP_FEED },
    'hideOnTimeline'
  );

  if (!config) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, { id }),
      method: config.apiMethod,
      params: config.apiParams
    });

    yield* deleteEntity(identity);

    yield* handleActionFeedback(response);
  } catch (err) {
    yield* handleActionError(err);
  }
}

const effects = [
  takeLatest('setting/reviewPosts/allowed', reviewPostsAllowed),
  takeEvery('setting/reviewPosts/hideItem', reviewPostsHideItem)
];

export default effects;
