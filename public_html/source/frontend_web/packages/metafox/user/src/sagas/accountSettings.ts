/**
 * @type: saga
 * name: settings.accountSettings
 */
import {
  FormSubmitAction,
  getGlobalContext,
  handleActionError,
  RELOAD_USER,
  getResourceAction,
  ItemLocalAction,
  AppResourceAction,
  handleActionConfirm,
  handleActionFeedback
} from '@metafox/framework';
import { get, isEmpty, isFunction } from 'lodash';
import { delay, put, take, takeEvery, takeLatest } from 'redux-saga/effects';
import {
  FETCH_SETTING,
  RELOAD_ACCOUNT,
  UPDATE_SETTING,
  FETCH_PAYMENT_SETTING,
  FETCH_MFA_SETTING,
  FETCH_MFA_FORM_SERVICE,
  REMOVE_MFA_SERVICE
} from '../actions/accountSettings';
import {
  getSettingFulfilled,
  updateFulfilled,
  updatePending,
  updateRejected
} from '../reducers/accountSettings';
import { getPaymentSettingFulfilled } from '../reducers/payment';
import { APP_USER, MULTI_FACTOR_AUTH } from '@metafox/user/constant';
import {
  getMFASettingFulfilled,
  updateActiveMFA
} from '../reducers/multiFactorAuth';

const apiUrl = '/account/setting';

export function* fetchData() {
  const { apiClient } = yield* getGlobalContext();

  try {
    const response = yield apiClient.get(apiUrl);
    const payload = response?.data?.data;

    if (payload) {
      yield put(getSettingFulfilled(payload));

      // required for done check
      // @see packages/metafox/core/src/sagas/formSubmit.tsx
      yield put({ type: 'setting/accountSettings/FETCH/DONE' });
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* updateData(action: FormSubmitAction) {
  const {
    meta = {},
    payload: { values }
  } = action;
  const { onFailure, onSuccess } = meta;

  try {
    yield put(updatePending());

    const { apiClient } = yield* getGlobalContext();

    yield apiClient.put(apiUrl, values);

    yield put(updateFulfilled(values));
    isFunction(onSuccess) && onSuccess();
  } catch (error) {
    yield* handleActionError(error);
    yield put(updateRejected());

    return (
      isFunction(onFailure) && onFailure(get(error, 'response.data.errors'))
    );
  }
}

function* reloadAccount() {
  yield put({ type: FETCH_SETTING });
  yield take(`${FETCH_SETTING}/DONE`);
  yield put({ type: RELOAD_USER });
  yield take(`${RELOAD_USER}/DONE`);
  yield put({ type: `${RELOAD_ACCOUNT}/DONE` });
  // wait until redux effected
  yield delay(1000);
}

export function* fetchPaymentData() {
  const { apiClient } = yield* getGlobalContext();
  const config = yield* getResourceAction(
    APP_USER,
    APP_USER,
    'getGatewaySettings'
  );

  if (!config?.apiUrl) return;

  const response = yield apiClient.get(config?.apiUrl);
  const payload = response.data?.data;

  if (payload) {
    yield put(getPaymentSettingFulfilled(payload));
  }
}

export function* fetchMFAData() {
  const { apiClient } = yield* getGlobalContext();
  const config = yield* getResourceAction(
    MULTI_FACTOR_AUTH,
    'service',
    'viewAll'
  );

  if (!config?.apiUrl) return;

  const response = yield apiClient.get(config?.apiUrl);
  const payload = response.data?.data;

  if (payload) {
    yield put(getMFASettingFulfilled(payload));
  }
}

export function* fetchMFAFormService(
  action: ItemLocalAction<{ service: string }>
) {
  const { dialogBackend, compactData } = yield* getGlobalContext();
  const { service } = action.payload;

  const config = yield* getResourceAction(
    MULTI_FACTOR_AUTH,
    'user_service',
    'setup'
  );

  if (!config?.apiUrl) return;

  const payload = yield dialogBackend.present({
    component: 'core.dialog.MultiStepForm',
    props: {
      dataSource: {
        apiUrl: config.apiUrl,
        apiParams: compactData(config?.apiParams, { service }),
        apiMethod: config.apiMethod
      },
      maxWidth: 'sm'
    }
  });

  if (payload) {
    yield put(updateActiveMFA(payload));
  }
}

export function* removeMFAService(
  action: ItemLocalAction<{ service: string }>
) {
  const { compactData, dialogBackend } = yield* getGlobalContext();
  const { service } = action.payload;

  const config = yield* getResourceAction(
    MULTI_FACTOR_AUTH,
    'user_service',
    'remove'
  );

  if (!config?.apiUrl) return;

  const payload = yield dialogBackend.present({
    component: 'core.dialog.MultiStepForm',
    props: {
      dataSource: {
        apiUrl: config.apiUrl,
        apiParams: compactData(config?.apiParams, { service }),
        apiMethod: config.apiMethod
      },
      maxWidth: 'sm'
    }
  });

  if (isEmpty(payload)) return;

  if (payload) {
    yield put(updateActiveMFA(payload));
  }
}

export function* logoutOtherDevices(
  action: ItemLocalAction<{
    dataSource: AppResourceAction;
    onSuccess: () => void;
  }>
) {
  const { apiClient } = yield* getGlobalContext();
  const { dataSource, onSuccess } = action.payload;

  if (!dataSource) return;

  const ok = yield* handleActionConfirm(dataSource);

  if (!ok) {
    onSuccess();

    return;
  }

  try {
    const response = yield apiClient.request({
      url: dataSource.apiUrl,
      method: dataSource.apiMethod
    });

    yield* handleActionFeedback(response);

    onSuccess();
  } catch (error) {
    onSuccess();
    yield* handleActionError(error);
  }
}

const sagas = [
  takeLatest(RELOAD_ACCOUNT, reloadAccount),
  takeLatest(FETCH_SETTING, fetchData),
  takeEvery(UPDATE_SETTING, updateData),
  takeLatest(FETCH_PAYMENT_SETTING, fetchPaymentData),
  takeLatest(FETCH_MFA_SETTING, fetchMFAData),
  takeLatest(FETCH_MFA_FORM_SERVICE, fetchMFAFormService),
  takeLatest(REMOVE_MFA_SERVICE, removeMFAService),
  takeLatest('user/logoutOtherDevices', logoutOtherDevices)
];

export default sagas;
