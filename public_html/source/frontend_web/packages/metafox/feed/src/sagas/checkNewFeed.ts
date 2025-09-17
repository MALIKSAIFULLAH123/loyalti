/**
 * @type: saga
 * name: saga.feed.checkNewFeed
 */

import {
  getGlobalContext,
  getItemActionConfig,
  ItemLocalAction,
  getItem
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { APP_FEED } from '@metafox/feed';
import { getTotalPriority } from './getTotalPriorityFeed';
import { getLastPin } from './getLastPinFeed';
import { getLastSponsor } from './getLastSponsorFeed';
import qs from 'query-string';

function* checkNews(
  action: ItemLocalAction & {
    payload: Record<string, any>;
  } & { meta: { onSuccess: () => void; onFailure: () => {} } }
) {
  const {
    payload: { pagingId },
    meta
  } = action;

  const { apiClient, compactData, location } = yield* getGlobalContext();
  const { apiUrl, apiMethod, apiParams } = yield* getItemActionConfig(
    { module_name: APP_FEED, resource_name: APP_FEED },
    'checkNew'
  );

  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};

  try {
    const pagingItem = yield* getItem(`pagination.${pagingId}`);
    const lastPin = yield* getLastPin(null, pagingId);
    const lastSponsor = yield* getLastSponsor(pagingId);
    const totalPriorityFeed = yield* getTotalPriority({
      pagingId,
      profileId: null
    });

    if (!pagingItem?.ids || !pagingItem?.ids?.length || !apiUrl) return;

    const last_feed_id = pagingItem?.ids[totalPriorityFeed || 0]?.split('.')[3];
    const last_pin_feed_id = lastPin?.id;
    const last_sponsor_feed_id = lastSponsor?.id;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        last_feed_id,
        last_pin_feed_id,
        last_sponsor_feed_id,
        sort: searchParams?.sort
      })
    });

    if (!response?.data) return;

    const { data } = response.data;

    if (data?.reload && meta?.onSuccess) {
      meta?.onSuccess();
    }
  } catch (error) {
    console.log(error);
  }
}

const sagas = [takeEvery('feed/checkNews', checkNews)];

export default sagas;
