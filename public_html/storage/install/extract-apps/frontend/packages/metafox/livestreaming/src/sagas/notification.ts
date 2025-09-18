/**
 * @type: saga
 * name: livestreaming.saga.notification
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  ItemLocalAction,
  handleActionFeedback,
  handleActionConfirm,
  AppResourceAction,
  patchEntity,
  handleActionError
} from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';

function* onNotification(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'onNotification');

  if (!config?.apiUrl) return;

  const ok = yield handleActionConfirm(config as AppResourceAction);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });
    const data = response?.data?.data;

    if (data) {
      yield* patchEntity(identity, {
        is_off_notification: data.is_off_notification
      });
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    handleActionError(error);
  }
}

function* offNotification(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'offNotification');

  if (!config?.apiUrl) return;

  const ok = yield handleActionConfirm(config as AppResourceAction);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });

    const data = response?.data?.data;

    if (data) {
      yield* patchEntity(identity, {
        is_off_notification: data.is_off_notification
      });
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    handleActionError(error);
  }
}

const sagas = [
  takeLatest('livestreaming/onNotification', onNotification),
  takeLatest('livestreaming/offNotification', offNotification)
];

export default sagas;
