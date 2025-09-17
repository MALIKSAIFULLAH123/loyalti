/**
 * @type: saga
 * name: ewallet.saga.withdrawalRequest
 */

import {
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  ItemLocalAction,
  getGlobalContext,
  handleActionError,
  fulfillEntity,
  getResourceConfig,
  handleActionFeedback,
  getResourceAction
} from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';
import {
  APP_EWALLET,
  EWALLET_STATISTIC,
  EWALLET_WITHDRAW_REQUEST
} from '../constants';
import { getPaymentSettingFulfilled } from '../reducers/payment';

function* cancelItemRequest(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, normalization } = yield* getGlobalContext();

  if (!item) return;

  const config = yield* getItemActionConfig(item, 'cancelItem');

  const { apiUrl, confirm, apiMethod } = config;

  const ok = yield handleActionConfirm(confirm);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(apiUrl, item),
      method: apiMethod || 'PATCH'
    });
    const data = response.data?.data;

    if (data) {
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* viewReason(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  const { dialogBackend, i18n } = yield* getGlobalContext();

  if (!item) return;

  yield dialogBackend.alert({
    title: i18n.formatMessage({ id: 'ewallet_reason' }),
    message: item?.reason
  });
}

function* newRequest(action) {
  const { meta } = action;

  try {
    const { dialogBackend, navigate } = yield* getGlobalContext();

    const dataSource = yield* getResourceConfig(
      APP_EWALLET,
      EWALLET_WITHDRAW_REQUEST,
      'addItem'
    );

    const data = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        maxWidth: 'sm'
      }
    });

    if (data) {
      navigate('/ewallet/request');

      if (meta?.onSuccess) {
        meta.onSuccess(data);
      }
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* fetchPaymentEwallet() {
  const { apiClient } = yield* getGlobalContext();
  const config = yield* getResourceAction(
    APP_EWALLET,
    EWALLET_STATISTIC,
    'getGatewaySettings'
  );

  if (!config?.apiUrl) return;

  const response = yield apiClient.get(config?.apiUrl);
  const payload = response.data?.data;

  if (payload) {
    yield put(getPaymentSettingFulfilled(payload));
  }
}

const sagas = [
  takeEvery('ewallet/cancelItem', cancelItemRequest),
  takeEvery('ewallet/viewReason', viewReason),
  takeEvery('ewallet/newRequest', newRequest),
  takeEvery('ewallet/paymentSettings', fetchPaymentEwallet)
];

export default sagas;
