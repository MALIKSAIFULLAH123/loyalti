/**
 * @type: saga
 * name: activitypoint.saga.managePoint
 */

import { openMultiStepForm } from '@metafox/form/sagas';
import {
  getGlobalContext,
  getItem,
  ItemLocalAction,
  getResourceConfig,
  handleActionError,
  getItemActionConfig,
  fulfillEntity,
  handleActionFeedback,
  handleActionConfirm
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import {
  APP_ACTIVITY,
  RESOURCE_POINT_CONVERSION_REQUEST,
  CONVERSION_REQUEST_GRID_NAME
} from '../constants';
import { randomId } from '@metafox/utils';

function* purchase(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { module_name: moduleName, resource_name: resourceName } = item;

  yield* openMultiStepForm({
    identity,
    resourceName,
    moduleName,
    actionName: 'purchaseItem',
    dialogProps: {
      fullWidth: false
    }
  });
}

function* conversionRequest(action) {
  const { meta } = action;

  try {
    const { dialogBackend, eventCenter } = yield* getGlobalContext();

    const dataSource = yield* getResourceConfig(
      APP_ACTIVITY,
      RESOURCE_POINT_CONVERSION_REQUEST,
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
      eventCenter.dispatch(
        `dataGridChangeRows_${CONVERSION_REQUEST_GRID_NAME}`,
        randomId()
      );

      if (meta?.onSuccess) {
        meta.onSuccess(data);
      }
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* cancelItemRequest(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, normalization } = yield* getGlobalContext();

  if (!item) return;

  const config = yield* getItemActionConfig(item, 'cancelItem');

  const { apiUrl, apiMethod } = config;

  const ok = yield* handleActionConfirm(config);

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
    title: i18n.formatMessage({ id: 'point_conversion_reason' }),
    message: item?.reason
  });
}

const sagas = [
  takeEvery('activityPoint/purchase', purchase),
  takeEvery('activitypoint/addItem', conversionRequest),
  takeEvery('activitypoint/cancelItem', cancelItemRequest),
  takeEvery('activitypoint/viewReason', viewReason)
];

export default sagas;
