/**
 * @type: saga
 * name: app.upgrade
 * bundle: admincp
 */
import { getGlobalContext, handleActionError } from '@metafox/framework';
import { get } from 'lodash';
import { takeLatest, select, put } from 'redux-saga/effects';

function* getUpgradeStart() {
  const { apiClient } = yield* getGlobalContext();

  try {
    const data = yield apiClient.get('/admincp/app/upgrade/start');

    const payload = get(data, 'data.data');

    yield put({ type: '@install/update', payload });
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* getProcessUpgrade() {
  const initialValues = yield select(state => state.app.upgrade);

  try {
    const data = {
      ...initialValues,
      recommendApps: undefined,
      steps: undefined
    };

    const { apiClient } = yield* getGlobalContext();

    const response = yield apiClient.post(
      '/admincp/app/upgrade/process-upgrade',
      data
    );

    const payload = get(response, 'data.data');

    yield put({ type: '@install/update', payload });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeLatest('@app/upgrade/start', getUpgradeStart),
  takeLatest('@app/upgrade/process/start', getProcessUpgrade)
];

export default sagas;
