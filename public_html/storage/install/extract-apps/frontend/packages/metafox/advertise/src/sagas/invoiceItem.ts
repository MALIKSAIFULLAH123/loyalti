/**
 * @type: saga
 * name: advertise.saga.invoiceItem
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  fulfillEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* cancelInvoiceItem(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl, compactData, normalization } =
    yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'cancelItem');

  if (!config.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, item)
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

const sagas = [takeEvery('advertise/cancelItem', cancelInvoiceItem)];

export default sagas;
