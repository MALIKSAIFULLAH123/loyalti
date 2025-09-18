/**
 * @type: saga
 * name: subscription_invoice.saga.cancelItem
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  ItemLocalAction,
  makeDirtyPaging,
  patchEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* cancelItem({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { dialogBackend } = yield* getGlobalContext();

  const dataSource = yield* getItemActionConfig(
    item,
    'getCancelSubscriptionForm'
  );

  if (!dataSource?.apiUrl) return;

  try {
    const data = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        pageParams: { id: item?.id }
      }
    });

    if (data) {
      yield* patchEntity(identity, data);
      yield* makeDirtyPaging('subscription-package');
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('subscription/getCancelSubscriptionForm', cancelItem)];

export default sagas;
