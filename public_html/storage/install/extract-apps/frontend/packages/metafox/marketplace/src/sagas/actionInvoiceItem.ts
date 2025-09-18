/**
 * @type: saga
 * name: marketplace_invoice.saga.paymentItem
 */

import { getGlobalContext, getItem, ItemLocalAction } from '@metafox/framework';
import { openMultiStepForm } from '@metafox/form/sagas';
import { takeEvery } from 'redux-saga/effects';

function* paymentPackage({ type, payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);
  const actionName = type.replace('marketplace/', '');

  if (!item) return;

  const { module_name: moduleName, resource_name: resourceName } = item;

  yield* openMultiStepForm({
    identity,
    resourceName,
    moduleName,
    actionName,
    dialogProps: {
      fullWidth: false
    }
  });
}

function* redirectToSoldInvoice({ payload }: ItemLocalAction) {
  const { navigate, compactUrl } = yield* getGlobalContext();

  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const url = compactUrl('/marketplace/invoice-sold?listing_id=:id', {
    id: item.id
  });

  navigate(url);
}

const sagas = [
  takeEvery('marketplace/getRepaymentForm', paymentPackage),
  takeEvery('marketplace/soldInvoice', redirectToSoldInvoice)
];

export default sagas;
