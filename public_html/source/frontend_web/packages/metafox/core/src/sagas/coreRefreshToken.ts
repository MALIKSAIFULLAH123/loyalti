/**
 * @type: saga
 * name: saga.coreRefreshToken
 */
import {
  APP_BOOTSTRAP_DONE,
  getGlobalContext,
  IS_INSTALLATION,
  REFRESH_TOKEN,
  STRATEGY_REFRESH_TOKEN
} from '@metafox/framework';
import moment from 'moment';
import { delay, put, takeLatest } from 'redux-saga/effects';

function* refreshToken() {
  const { cookieBackend } = yield* getGlobalContext();

  if (!cookieBackend.get('refreshToken')) return;

  try {
    yield put({ type: REFRESH_TOKEN, payload: { reload: false } });
  } catch (error) {
    // implicit error
  }
}

function* strategyRefreshToken() {
  const { cookieBackend } = yield* getGlobalContext();
  const expiredTokenTime = cookieBackend.get('dateExpiredToken');

  if (IS_INSTALLATION) {
    return;
  }

  if (expiredTokenTime) {
    const timeLeft = parseInt(expiredTokenTime) - moment().unix();

    if (timeLeft <= 0) {
      return;
    }

    // refresh before 1 hour token expired
    const timeThreshold = 60 * 60;
    const delayTime = (timeLeft - timeThreshold) * 1000;

    if (delayTime <= 0) {
      // min 10 seconds
      yield delay(Math.max(timeLeft, 10000));
      yield* refreshToken();

      return;
    }

    yield delay(delayTime);
    yield* refreshToken();
  }
}

const sagas = [
  takeLatest([APP_BOOTSTRAP_DONE, STRATEGY_REFRESH_TOKEN], strategyRefreshToken)
];

export default sagas;
