/**
 * @type: saga
 * name: subscription.saga.presentDialogPackages
 */

import {
  getGlobalContext,
  handleActionError,
  getResourceAction,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import {
  APP_SUBSCRIPTION,
  RESOURCE_SUBSCRIPTION_PACKAGE
} from '@metafox/subscription';

function* presentDialogPackages(
  action: ItemLocalAction & {
    payload: { hasComparison: boolean; relatedFieldName?: string };
  }
) {
  const { payload, meta } = action;
  const { hasComparison, relatedFieldName } = payload || {};

  const { dialogBackend } = yield* getGlobalContext();

  const dataSource = yield* getResourceAction(
    APP_SUBSCRIPTION,
    RESOURCE_SUBSCRIPTION_PACKAGE,
    'viewAll'
  );

  if (!dataSource?.apiUrl) return;

  try {
    yield dialogBackend.present({
      component: 'subscription.dialog.PackagesDialog',
      props: {
        dataSource,
        hasComparison,
        relatedFieldName,
        meta
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('subscription/presentDialogPackages', presentDialogPackages)
];

export default sagas;
