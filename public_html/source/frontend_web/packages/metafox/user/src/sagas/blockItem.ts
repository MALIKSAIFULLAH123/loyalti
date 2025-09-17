/**
 * @type: saga
 * name: saga.user.blockItem
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity,
  reloadEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* blockItem(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  const { apiClient, compactUrl, compactData, redirectTo, getPageParams } =
    yield* getGlobalContext();
  const config = yield* getItemActionConfig(item, 'blockItem');
  const { _pageType } = getPageParams();

  if (!config?.apiUrl) return;

  const { is_blocked: value } = item;

  // item is blocked
  if (value) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    yield* patchEntity(identity, { is_blocked: true });
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, item)
    });

    yield* handleActionFeedback(response);
    yield* reloadEntity(identity);

    if (_pageType === 'profile') {
      const redirectToURL =
        response.data?.data?.redirectTo || '/settings/blocked';

      redirectTo(redirectToURL);
    }
  } catch (error) {
    yield* patchEntity(identity, { is_blocked: value });
    yield* handleActionError(error);
  }
}

function* unblockItem(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(item, 'unblockItem');

  if (!config?.apiUrl) return;

  const { is_blocked: value } = item;

  // item is blocked
  if (!value) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    yield* patchEntity(identity, { is_blocked: false });
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, item)
    });

    yield* handleActionFeedback(response);
    yield* reloadEntity(identity);
  } catch (error) {
    yield* patchEntity(identity, { is_blocked: value });
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('blockItem', blockItem),
  takeEvery('unblockItem', unblockItem)
];

export default sagas;
