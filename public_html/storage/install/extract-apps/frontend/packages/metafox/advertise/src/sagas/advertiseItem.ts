/**
 * @type: saga
 * name: advertise.saga.advertiseItem
 */

import {
  deleteEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  getResourceConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  LAYOUT_EDITOR_TOGGLE,
  patchEntity
} from '@metafox/framework';
import { isEmpty } from 'lodash';
import { takeEvery, all } from 'redux-saga/effects';
import { APP_NAME } from '../constants';

function* activeItem(
  action: ItemLocalAction & { payload: { active: 0 | 1 } }
) {
  const { identity, active } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'activeItem');

  if (!config.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, { active })
    });

    const data = response.data?.data;

    if (data?.is_active) {
      yield* patchEntity(identity, {
        is_active: data.is_active
      });
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* paymentItem(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { dialogBackend, compactUrl } = yield* getGlobalContext();

  const dataSource = yield* getItemActionConfig(item, 'paymentItem');

  if (!dataSource.apiUrl) return;

  const ok = yield* handleActionConfirm(dataSource);

  if (!ok) return;

  try {
    yield dialogBackend.present({
      component: 'core.dialog.MultiStepForm',
      props: {
        dataSource: { apiUrl: compactUrl(dataSource.apiUrl, item) },
        maxWidth: 'sm',
        fullWidth: false
      }
    });

    // yield* handleActionFeedback(data);
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* updateImpression(action: ItemLocalAction & { payload: any }) {
  const { apiClient, compactUrl, localStore } = yield* getGlobalContext();

  const config = yield* getResourceConfig(
    APP_NAME,
    APP_NAME,
    'updateTotalItem'
  );

  const item = action.payload;

  const isEditMode = localStore.get(LAYOUT_EDITOR_TOGGLE);

  if (item._viewed || isEditMode || !config.apiUrl) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: { type: 'impression' }
    });

    if (response?.data && item._identity) {
      const status = response.data?.status;

      if (status === 'success') {
        yield* patchEntity(item._identity, { _viewed: true });
      }
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* updateTotalImpression(action: ItemLocalAction & { payload: any }) {
  const data = action.payload;

  if (isEmpty(data)) return;

  try {
    yield all(data.map(item => updateImpression({ payload: item } as any)));
  } catch (error) {
    // yield* handleActionError(error);
  }
}

function* updateTotalClick(action: ItemLocalAction & { payload: any }) {
  const { apiClient, compactUrl, localStore } = yield* getGlobalContext();
  const item = action.payload;

  const isEditMode = localStore.get(LAYOUT_EDITOR_TOGGLE);

  if (isEditMode || !item) return;

  const config = yield* getResourceConfig(
    APP_NAME,
    APP_NAME,
    'updateTotalItem'
  );

  if (!config.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: { type: 'click' }
    });

    const data = response.data?.data;

    if (data) {
    }
  } catch (error) {
    // yield* handleActionError(error);
  }
}

function* hideItem(action: ItemLocalAction & { payload: any }) {
  const item = action.payload;

  if (!item) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getResourceConfig(APP_NAME, APP_NAME, 'hideItem');

  if (!config.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });

    yield* deleteEntity(item?._identity);
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('advertise/activeItem', activeItem),
  takeEvery('advertise/paymentItem', paymentItem),
  takeEvery('advertise/updateTotalImpression', updateTotalImpression),
  takeEvery('advertise/updateImpression', updateImpression),
  takeEvery('advertise/updateTotalClick', updateTotalClick),
  takeEvery('advertise/hideItem', hideItem)
];

export default sagas;
