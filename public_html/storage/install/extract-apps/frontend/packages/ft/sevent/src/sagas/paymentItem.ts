/**
 * @type: saga
 * name: sevent.saga.paymentItem
 */
import { getItem, ItemLocalAction } from '@metafox/framework';
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

const sagas = [takeEvery('sevent/paymentItem', paymentPackage)];

export default sagas;
