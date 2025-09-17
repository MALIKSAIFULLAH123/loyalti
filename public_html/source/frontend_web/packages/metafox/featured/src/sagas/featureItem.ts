/**
 * @type: saga
 * name: featured.saga.featureItems
 */

import {
  deleteEntity,
  fulfillEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  getResourceAction,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import {
  APP_NAME,
  FEATURED_ITEM,
  RESOURCE_FEATURED_ITEM,
  UN_FEATURED_ITEM
} from '../constants';
import { compactUrl } from '@metafox/utils';

export function* unfeatureItemNew(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield getItem(identity);

  if (!item) return;

  const { apiClient, dialogBackend, compactUrl, normalization } =
    yield* getGlobalContext();
  const { is_featured: value } = item;

  const config = yield* getItemActionConfig(item, 'unfeatureItemNew');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    yield* patchEntity(identity, { is_featured: Boolean(UN_FEATURED_ITEM) });

    const response = yield apiClient.request({
      method: config.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: config.apiParams
    });

    const data = response.data?.data;

    if (data) {
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, { is_featured: value });
    yield* handleActionError(error);
  }
}

export function* featureFreeItem(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield getItem(identity);

  if (!item) return;

  const { apiClient, dialogBackend, compactUrl, normalization } =
    yield* getGlobalContext();
  const { is_featured: value } = item;

  const config = yield* getItemActionConfig(item, 'featureFreeItem');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    yield* patchEntity(identity, { is_featured: Boolean(FEATURED_ITEM) });
    const response = yield apiClient.request({
      method: config.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: config.apiParams
    });

    const data = response.data?.data;

    if (data) {
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, { is_featured: value });
    yield* handleActionError(error);
  }
}

export function* itemActionMenuFeatureItem(action: ItemLocalAction) {
  switch (action.type) {
    case 'featureFreeItem':
      yield* featureFreeItem(action);
      break;
    case 'unfeatureItemNew':
      yield* unfeatureItemNew(action);
      break;
  }
}

export function* purchaseFeatureItem(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield getItem(identity);

  if (!item) return;

  const { dialogBackend, navigate } = yield* getGlobalContext();

  const config = yield* getResourceAction(
    APP_NAME,
    RESOURCE_FEATURED_ITEM,
    'addItem'
  );

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const pageUrl = compactUrl(config.pageUrl, item);

    navigate(pageUrl);
  } catch (error) {}
}

export function* cancelItem(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'cancelItem');

  if (!config) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: config?.apiParams
    });

    if (response) {
      yield* deleteEntity(identity);
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* paymentItem(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield getItem(identity);

  if (!item) return;

  const { dialogBackend, getPageParams } = yield* getGlobalContext();
  const pageParams = getPageParams();

  const config = yield* getItemActionConfig(item, 'paymentItem');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    yield dialogBackend.present({
      component: 'core.dialog.MultiStepForm',
      props: {
        dataSource: config,
        pageParams: { ...pageParams, ...item }
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('purchaseFeatureItem', purchaseFeatureItem),
  takeEvery('featureFreeItem', featureFreeItem),
  takeEvery('unfeatureItemNew', unfeatureItemNew),
  takeEvery('featured/cancelItem', cancelItem),
  takeEvery('featured/paymentItem', paymentItem)
];

export default sagas;
