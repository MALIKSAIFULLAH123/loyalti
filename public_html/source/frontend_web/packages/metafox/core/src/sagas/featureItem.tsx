/**
 * @type: saga
 * name: core.featureItem
 */
import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { FEATURE_ITEM, UN_FEATURE_ITEM } from '../constant';

export function* unfeatureItem(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield getItem(identity);

  if (!item) return;

  const { apiClient, dialogBackend, compactUrl } = yield* getGlobalContext();
  const { is_featured: value } = item;

  const config = yield* getItemActionConfig(item, 'featureItem');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    yield* patchEntity(identity, { is_featured: Boolean(UN_FEATURE_ITEM) });

    const response = yield apiClient.request({
      method: config.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: {
        feature: UN_FEATURE_ITEM
      }
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, { is_featured: value });
    yield* handleActionError(error);
  }
}

export function* featureItem(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield getItem(identity);

  if (!item) return;

  const { apiClient, dialogBackend, compactUrl } = yield* getGlobalContext();
  const { is_featured: value } = item;

  const config = yield* getItemActionConfig(item, 'featureItem');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    yield* patchEntity(identity, { is_featured: Boolean(FEATURE_ITEM) });
    const response = yield apiClient.request({
      method: config.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: {
        feature: FEATURE_ITEM
      }
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, { is_featured: value });
    yield* handleActionError(error);
  }
}

export function* itemActionMenuFeatureItem(action: ItemLocalAction) {
  switch (action.type) {
    case 'featureItem':
      yield* featureItem(action);
      break;
    case 'unfeatureItem':
      yield* unfeatureItem(action);
      break;
  }
}

const sagas = [
  takeEvery('featureItem', featureItem),
  takeEvery('unfeatureItem', unfeatureItem)
];

export default sagas;
