/**
 * @type: saga
 * name: core.registerFCM
 */
import {
  getGlobalContext,
  ItemLocalAction,
  getResourceAction
} from '@metafox/framework';
import { takeEvery, delay } from 'redux-saga/effects';

enum DisableMode {
  Default = '0',
  Skip = '1',
  Accept = '2'
}

function* registerFCM(action: ItemLocalAction<{ token: string }>) {
  const { token } = action.payload;

  const { apiClient, compactData, cookieBackend } = yield* getGlobalContext();

  const config = yield* getResourceAction(
    'authorization',
    'user_device',
    'addItem'
  );

  if (!config?.apiUrl) return false;

  try {
    const isRemember = cookieBackend.get('refreshToken');

    const response = yield apiClient.request({
      url: config?.apiUrl,
      method: config?.apiMethod || 'post',
      params: compactData(config?.apiParams, {
        token_source: 'firebase',
        device_token: token
      })
    });
    const { status } = response.data || {};

    if (status === 'success') {
      cookieBackend.set('fcm-token', token, {
        expires: isRemember ? 30 : undefined
      });
      cookieBackend.set('fcm-notification', DisableMode.Accept, {
        expires: isRemember ? 30 : undefined
      });
    }
  } catch (err) {
    console.log(err);
  }
}

const delayTime = 10000;

function* requestBanner(action: ItemLocalAction<{}, { cb?: () => void }>) {
  const { meta } = action || {};
  yield delay(delayTime);
  meta.cb && meta.cb();
}

const sagas = [
  takeEvery('@registerFCM', registerFCM),
  takeEvery('core/fcm/requestBanner', requestBanner)
];

export default sagas;
