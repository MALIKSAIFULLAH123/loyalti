/**
 * @type: saga
 * name: captcha.token
 */

import { LocalAction } from '@metafox/framework';
import { put, call, takeLatest } from 'redux-saga/effects';

function* getReCaptchaToken({ payload }: LocalAction<{ siteKey: string }>) {
  try {
    const token = yield call(window.grecaptcha.execute, payload.siteKey);

    yield put({
      type: payload?.actionSuccess || 'captcha/token/response',
      payload: token
    });
  } catch (error) {
    yield put({
      type: payload?.actionSuccess || 'captcha/token/response',
      payload: ''
    });
  }
}

export default takeLatest('captcha/recaptcha/token', getReCaptchaToken);
