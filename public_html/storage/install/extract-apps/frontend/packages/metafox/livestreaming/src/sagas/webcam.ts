/**
 * @type: saga
 * name: livestreaming.saga.webcam
 */

import { getGlobalContext, getSession } from '@metafox/framework';
import { put, takeLatest } from 'redux-saga/effects';
import {
  LivestreamConfig,
  InitResultShape
} from '@metafox/livestreaming/types';

function* bootstrap(action) {
  const { onSuccess } = action?.meta || {};
  const { livestreamingSocket, cookieBackend, getSetting } =
    yield* getGlobalContext();
  const { user } = yield* getSession();
  const config = getSetting<LivestreamConfig>('livestreaming');

  if (!user?.id) return;

  if (!config || !config?.socketUrl) return;

  try {
    config.ddpDebug = true;
    config.debug = true;
    config.accessToken = cookieBackend.get('token');
    config.userId = user.id.toString();
    config.socketUrl = `${config.socketUrl
      .replace(/^http/, 'ws')
      .replace(/(\/+)$/, '')}/websocket`;
    const data: InitResultShape = yield livestreamingSocket.init(user, config);

    if (data) {
      onSuccess && onSuccess();
      yield put({ type: 'livestreaming/socket/inited', payload: data });
    }
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log('bootstrapLivestreamingSocket error', error);
  }
}

const sagas = [takeLatest('livestreaming/socket/init', bootstrap)];

export default sagas;
