/**
 * @deprecated
 * @type: saga
 * name: user.logout
 */
import {
  IS_ADMINCP,
  getGlobalContext,
  getSession,
  LocalAction
} from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';
import { cloneDeep } from 'lodash';

/**
 * Deprecated by cause redirect two slow and User see flash screen
 * @returns
 */
export function* logoutSaga({
  payload
}: LocalAction<{ cancelAccount?: boolean }>) {
  const { redirectTo, apiClient, cookieBackend, getSetting } =
    yield* getGlobalContext();
  const { cancelAccount } = payload || {};

  const token = yield cookieBackend.get('token');
  const deviceToken = yield cookieBackend.get('fcm-token');
  const redirect_after_logout = getSetting('user.redirect_after_logout');
  const { user } = yield* getSession();

  if (user?.id) {
    yield put({
      type: 'session/addAccount',
      payload: cloneDeep(user)
    });
  }

  if (!token) return;

  yield cookieBackend.remove('token');
  yield cookieBackend.remove('refreshToken');
  yield cookieBackend.remove('dateExpiredToken');

  if (deviceToken) {
    // clear if has token, if not keep fcm-notification for dismiss alert
    yield cookieBackend.remove('fcm-notification');
    yield cookieBackend.remove('fcm-token');
  }

  // TO DO send request to notify logged out.
  try {
    // remove service worker
    navigator.serviceWorker.getRegistrations().then(registrations => {
      for (const registration of registrations) {
        registration.unregister();
      }
    });

    if (!cancelAccount) {
      // send request logout
      yield apiClient.request({
        url: '/auth/logout',
        method: 'GET',
        params: { device_uid: deviceToken },
        headers: {
          Authorization: `Bearer ${token}`
        }
      });
    }
  } catch (err) {
    // do nothing
  }

  const redirectUrl = IS_ADMINCP
    ? process.env.MFOX_ADMINCP_URL
    : process.env.MFOX_SITE_URL;

  redirectTo(redirect_after_logout || redirectUrl || '/');
}

const sagaEffect = takeEvery('@logout', logoutSaga);

export default sagaEffect;
