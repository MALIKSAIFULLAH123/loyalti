/**
 * @type: saga
 * name: core.sponsorItem
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
import { SPONSOR_ITEM, UN_SPONSOR_ITEM } from '../constant';

export function* sponsorItem(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, dialogBackend, compactUrl } = yield* getGlobalContext();
  const { is_sponsor: value } = item;
  const config = yield* getItemActionConfig(item, 'sponsorItem');

  if (!config) return;

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: {
        sponsor: SPONSOR_ITEM
      }
    });

    const data = response?.data?.data;

    if (data) {
      yield* patchEntity(identity, {
        is_sponsor: data?.is_sponsor,
        extra: { ...item.extra, ...(data?.extra || {}) }
      });
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, { is_sponsor: value });
    yield* handleActionError(error);
  }
}

export function* unsponsorItem(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, dialogBackend, compactUrl } = yield* getGlobalContext();
  const { is_sponsor: value } = item;
  const config = yield* getItemActionConfig(item, 'sponsorItem');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: {
        sponsor: UN_SPONSOR_ITEM
      }
    });
    const data = response?.data?.data;

    if (data) {
      yield* patchEntity(identity, {
        is_sponsor: data?.is_sponsor,
        extra: { ...item.extra, ...(data?.extra || {}) }
      });
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, { is_sponsor: value });
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('sponsorItem', sponsorItem),
  takeEvery('unsponsorItem', unsponsorItem)
];

export default sagas;
