/**
 * @type: saga
 * name: marketplace.saga.paymentItem
 */

import {
  fulfillEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { openMultiStepForm } from '@metafox/form/sagas';

function* paymentPackage(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { module_name: moduleName, resource_name: resourceName } = item;

  yield* openMultiStepForm({
    identity,
    resourceName,
    moduleName,
    actionName: 'paymentItem',
    dialogProps: {
      fullWidth: false
    }
  });
}

function* cancelOnExpiredListing(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { dialogBackend, normalization } = yield* getGlobalContext();
  const dataSource = yield* getItemActionConfig(item, 'cancelOnExpiredListing');

  if (!item) return;

  try {
    const response = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        pageParams: { id: item?.id }
      }
    });

    if (response) {
      const result = normalization.normalize(response);
      yield* fulfillEntity(result.data);
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('marketplace/paymentItem', paymentPackage),
  takeEvery('marketplace/cancelOnExpiredListing', cancelOnExpiredListing)
];

export default sagas;
