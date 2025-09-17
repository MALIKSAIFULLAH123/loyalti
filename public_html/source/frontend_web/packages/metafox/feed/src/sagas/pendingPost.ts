/**
 * @type: saga
 * name: saga.feed.pendingPost
 */

import {
  deleteEntity,
  getGlobalContext,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  makeDirtyPaging,
  getItem
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { compactUrl } from '@metafox/utils';
import { APP_FEED } from '@metafox/feed';

function* declinePendingAndBlockAuthor(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const { dialogBackend } = yield* getGlobalContext();
  const item = yield* getItem(identity);

  try {
    const dataSource = yield* getItemActionConfig(
      { module_name: APP_FEED, resource_name: APP_FEED },
      'declinePendingAndBlockAuthor'
    );

    const ok = yield* handleActionConfirm(dataSource, item);

    if (!ok) return;

    const response = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        pageParams: item
      }
    });

    if (response) {
      yield* deleteEntity(identity);
      yield* makeDirtyPaging('group-block');
      yield* makeDirtyPaging('group-member');
      yield* handleActionFeedback(response);
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* keepPendingPost(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const { apiClient } = yield* getGlobalContext();

  const dataSource = yield* getItemActionConfig(
    { module_name: APP_FEED, resource_name: APP_FEED },
    'approvePending'
  );

  const { apiUrl, apiMethod } = dataSource;

  try {
    const id = identity.split('.')[3];

    const response = yield apiClient.request({
      method: apiMethod,
      url: compactUrl(apiUrl, { id })
    });

    yield* deleteEntity(identity);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* declinePendingPost(
  action: ItemLocalAction & {
    payload: Record<string, any>;
  } & { meta: { onSuccess: () => void; onFailure: () => {} } }
) {
  const {
    payload: { identity }
  } = action;

  const { apiClient } = yield* getGlobalContext();
  const { apiUrl, apiMethod } = yield* getItemActionConfig(
    { module_name: APP_FEED, resource_name: APP_FEED },
    'declinePending'
  );

  try {
    const id = identity.split('.')[3];

    const response = yield apiClient.request({
      method: apiMethod,
      url: compactUrl(apiUrl, { id })
    });

    yield* deleteEntity(identity);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('feed/declinePendingPost', declinePendingPost),
  takeEvery('feed/declinePendingAndBlockAuthor', declinePendingAndBlockAuthor),
  takeEvery('feed/approvePendingPost', keepPendingPost)
];

export default sagas;
