/**
 * @type: saga
 * name: livestreaming.saga.endLive
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  ItemLocalAction,
  handleActionFeedback,
  handleActionError,
  handleActionConfirm
} from '@metafox/framework';
import { takeLatest, put } from 'redux-saga/effects';
import { APP_LIVESTREAM, RESOURCE_LIVE_VIDEO } from '../constants';

type ActionType = ItemLocalAction & {
  payload: {
    id: number;
  };
};

function* endLive(action: ActionType) {
  const { id } = action.payload;
  const identity = `${APP_LIVESTREAM}.entities.${RESOURCE_LIVE_VIDEO}.${id}`;
  const item = yield* getItem(identity);

  if (!item || !item?.is_streaming) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'endLive');

  if (!config.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });

    if (response) {
      yield put({
        type: 'livestreaming/updateStatusOffline',
        payload: { identity }
      });
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    handleActionError(error);
  }
}

function* forceEndLive(action: ActionType) {
  const { id } = action.payload;
  const identity = `${APP_LIVESTREAM}.entities.${RESOURCE_LIVE_VIDEO}.${id}`;
  const item = yield* getItem(identity);

  if (!item || !item?.is_streaming) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'endLive');

  if (!config.apiUrl) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });

    if (response) {
      yield put({
        type: 'livestreaming/updateStatusOffline',
        payload: { identity }
      });
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    handleActionError(error);
  }
}

const sagas = [
  takeLatest('livestreaming/end-live', endLive),
  takeLatest('livestreaming/end-live/force', forceEndLive)
];

export default sagas;
