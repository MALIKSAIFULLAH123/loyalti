/**
 * @type: saga
 * name: subscription_invoice.saga.paymentItem
 */

import { getItem, ItemLocalAction } from '@metafox/framework';
import { openMultiStepForm } from '@metafox/form/sagas';
import { takeEvery } from 'redux-saga/effects';

function* paymentPackage({ type, payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);
  const actionName = type.replace('subscription/', '');

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

const sagas = [
  takeEvery(
    [
      'subscription/getUpgradeSubscriptionForm',
      'subscription/getPayNowSubscriptionForm',
      'subscription/getRenewSubscriptionForm'
    ],
    paymentPackage
  )
];

export default sagas;
