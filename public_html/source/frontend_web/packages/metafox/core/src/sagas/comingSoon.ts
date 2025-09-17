/**
 * @type: saga
 * name: saga.core.comingSoon
 */
import { getGlobalContext } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* comingSoon() {
  const { dialogBackend } = yield* getGlobalContext();

  yield dialogBackend.comingSoon();
}

const sagas = [takeEvery('comingSoon', comingSoon)];

export default sagas;
