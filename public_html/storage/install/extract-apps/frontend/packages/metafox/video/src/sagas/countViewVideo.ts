/**
 * @type: saga
 * name: video.saga.countViewVideo
 */

import {
  getGlobalContext,
  getResourceAction,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { APP_VIDEO } from '@metafox/video/constant';

export function* countViewVideo(action: ItemLocalAction<{ id: number }>) {
  const { id } = action.payload;
  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getResourceAction(
    APP_VIDEO,
    APP_VIDEO,
    'increaseViewItem'
  );
 
  if (!config) return;

  try {
    yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, { id })
    });
  } catch (error) {}
}

const sagas = [takeEvery('video/countViewVideo', countViewVideo)];

export default sagas;
