/**
 * @type: saga
 * name: feed.hideFeedItem
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
import { HIDE_ITEM } from '../constant';

function* hideItem(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl } = yield* getGlobalContext();

  if (!item) return;

  const config = yield* getItemActionConfig(item, 'hideItem');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_hidden_type: hideValue, is_just_hide: justHideValue } = item;

  try {
    yield* patchEntity(identity, {
      is_hidden_type: HIDE_ITEM,
      is_just_hide: true
    });
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config?.apiMethod
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield* handleActionError(error);
  }
}

function* undoHideItem(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, dialogBackend, compactUrl } = yield* getGlobalContext();

  if (!item) return;

  const config = yield* getItemActionConfig(item, 'undoHideItem');

  if (!config?.apiUrl) return;

  const { is_hidden_type: hideValue, is_just_hide: justHideValue } = item;
  const apiUrl = compactUrl(config.apiUrl, item);

  try {
    yield* patchEntity(identity, {
      is_hidden_type: HIDE_ITEM,
      is_just_hide: false
    });
    yield apiClient.request({
      url: apiUrl,
      method: config?.apiMethod
    });
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield dialogBackend.alert({ message: error.response.data.error.message });
  }

  // TODO implements call featured api
}

const sagas = [
  takeEvery('undoHideItem', undoHideItem),
  takeEvery('hideItem', hideItem)
];

export default sagas;
