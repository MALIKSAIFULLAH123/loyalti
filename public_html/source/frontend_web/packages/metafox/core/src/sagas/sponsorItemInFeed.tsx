/**
 * @type: saga
 * name: core.sponsorItemInFeed
 */
import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { SPONSOR_ITEM_IN_FEED, UN_SPONSOR_ITEM_IN_FEED } from '../constant';

export function* sponsorItemInFeed(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(item, 'sponsorItemInFeed');

  try {
    const response = yield apiClient.request({
      method: config.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: {
        sponsor: SPONSOR_ITEM_IN_FEED
      }
    });

    const data = response?.data?.data;

    if (data) {
      yield* patchEntity(identity, {
        sponsor_in_feed: data?.sponsor_in_feed,
        extra: { ...item.extra, ...(data?.extra || {}) }
      });
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* unsponsorItemInFeed(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(item, 'sponsorItemInFeed');

  try {
    const response = yield apiClient.request({
      method: config.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: {
        sponsor: UN_SPONSOR_ITEM_IN_FEED
      }
    });

    const data = response?.data?.data;

    if (data) {
      yield* patchEntity(identity, {
        sponsor_in_feed: data?.sponsor_in_feed,
        extra: { ...item.extra, ...(data?.extra || {}) }
      });
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('sponsorItemInFeed', sponsorItemInFeed),
  takeEvery('unsponsorItemInFeed', unsponsorItemInFeed)
];

export default sagas;
