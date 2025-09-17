/**
 * @type: saga
 * name: @metafox/installation
 * bundle: installation
 */

import {
  APP_BOOTSTRAP_DONE,
  getGlobalContext,
  GlobalState,
  handleActionError,
  LocalAction
} from '@metafox/framework';
import { get } from 'lodash';
import { put, select, delay, take, all, takeEvery } from 'redux-saga/effects';

function* loadSetup(baseURL?: string) {
  const { apiClient } = yield* getGlobalContext();

  if (baseURL) {
    apiClient.defaults.baseURL = baseURL;
  }

  const response = yield apiClient.get('install/?step=start', {
    responseType: 'json'
  });

  return response.data.data;
}

function* loadSetupWizard() {
  try {
    let payload = yield* loadSetup();

    if (!payload) {
      let baseURL = window.prompt('Enter API Url, example: api.local.test');

      if (!baseURL) {
        throw new Error(
          'Could not installed to your site, contact metafox support team for more information.'
        );
        // stop and run failure.
      }

      if (!/https?:\/\//.test(baseURL)) {
        baseURL = `${window.location.protocol}//${baseURL}`;
      }

      // retry failed issue when separate api domain.
      payload = yield* loadSetup(`${baseURL}/api/v1`);
    }

    yield put({ type: '@install/update', payload });
  } catch (err) {
    yield put({ type: '@install/update', payload: { failed: true } });
  }

  yield put({ type: '@bootstrap/installation/DONE' });
}

function* bootstrapSetupWizard() {
  yield all([
    take('@bootstrap/theme/DONE'),
    take('@bootstrap/installation/DONE')
  ]);

  yield put({ type: APP_BOOTSTRAP_DONE, payload: { loaded: true } });
}

function* nextStep() {
  const { steps, currentStep: found } = yield select(
    (state: GlobalState) => state.installation
  );

  if (found > steps.length - 1) return;

  const next = steps[found + 1];

  if (!next) return;

  yield put({
    type: '@install/update',
    payload: { currentStep: found + 1, verifiedStep: found + 1 }
  });

  // wait a reducer applied.
  yield delay(250);

  const url = `/install?step=${next.id}`;

  const { navigate } = yield* getGlobalContext();

  if (url) {
    navigate(url, { replace: true });
  }
}

function* prevStep() {
  const { steps, currentStep: found } = yield select(
    (state: GlobalState) => state.installation
  );

  if (found < 1) return;

  const next = steps[found - 1];

  if (!next) return;

  yield put({ type: '@install/update', payload: { currentStep: found - 1 } });

  const url = `/install?step=${next.id}`;

  const { navigate } = yield* getGlobalContext();

  if (url) {
    navigate(url, { replace: true });
  }
}

function* fetchProcessInstall({ payload }: LocalAction<{ apiUrl: string }>) {
  try {
    const { apiClient } = yield* getGlobalContext();
    const initialValues = yield select(
      (state: GlobalState) => state.installation
    );

    const response = yield apiClient.post(payload.apiUrl, {
      ...initialValues,
      recommendApps: undefined
    });

    const data = get(response, 'data.data');

    yield put({ type: '@install/update', payload: { processList: data } });
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* fetchApps({ payload }: LocalAction<{ apiUrl: string }>) {
  try {
    const { apiClient } = yield* getGlobalContext();
    const initialValues = yield select(
      (state: GlobalState) => state.installation
    );
    const { recommendAppsLoaded } = initialValues;

    if (recommendAppsLoaded) {
      return;
    }

    const response = yield apiClient.post('/install?step=select-apps', {
      license: initialValues.license
    });

    const data = get(response, 'data.data');

    yield put({
      type: '@install/update',
      payload: {
        ...data,
        recommendAppsLoaded: true
      }
    });
  } catch (err) {
    yield* handleActionError(err);
  }
}

const sagas = [
  takeEvery('SetupWizard@bootstrap', bootstrapSetupWizard),
  takeEvery('SetupWizard@bootstrap', loadSetupWizard),
  takeEvery('@install/prev', prevStep),
  takeEvery('@install/next', nextStep),
  takeEvery('@install/process-list/fetch', fetchProcessInstall),
  takeEvery('@install/select-apps/fetch', fetchApps)
];

export default sagas;
