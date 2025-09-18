/**
 * @type: saga
 * name: subscription_package.saga.paymentItem
 */

import { getItem, ItemLocalAction, makeDirtyPaging } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { openMultiStepForm } from '@metafox/form/sagas';

function* refreshListing() {
  yield* makeDirtyPaging('subscription-package');
  yield* makeDirtyPaging('my-subscription-invoice');
}

function* paymentPackage(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { module_name: moduleName, resource_name: resourceName } = item;

  yield* openMultiStepForm({
    identity,
    resourceName,
    moduleName,
    actionName: 'getPaymentPackageForm',
    dialogProps: {
      fullWidth: false
    }
  });
}

const sagas = [
  takeEvery('subscription/paymentPackage', paymentPackage),
  takeEvery('subscription/listing/reload', refreshListing)
];

export default sagas;
