/**
 * @type: saga
 * name: pinFeed
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  PAGINATION_UNSHIFT,
  patchEntity
} from '@metafox/framework';
import { get } from 'lodash';
import { call, put, takeEvery } from 'redux-saga/effects';
import { getTotalPins } from './getTotalPins';
import {
  getFeedPagingIdActive,
  putPagingFeedRefresh
} from '@metafox/feed/sagas';

function* pinItem({ payload: { identity } }: ItemLocalAction) {
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, getSetting, i18n } = yield* getGlobalContext();
  const limit = parseInt(getSetting('activity.feed.total_pin_in_profile'));
  const owner_id = item?.owner_id;

  if (!item) return;

  const config = yield call(getItemActionConfig, item, 'pinItem');

  if (!config?.apiUrl) return;

  const total = yield* getTotalPins(parseInt(owner_id));
  const isOverLimit = limit > 0 && total >= limit;
  const configConfirm = isOverLimit
    ? {
        confirm: {
          message: i18n.formatMessage({
            id: 'over_limit_total_pinned_feeds_description'
          })
        }
      }
    : {};

  const ok = yield* handleActionConfirm({ ...config, ...configConfirm });

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config?.apiMethod,
      params: {
        owner_id
      }
    });

    const pins = get(response, 'data.data');

    yield call(patchEntity, identity, { pins });

    yield call(handleActionFeedback, response);

    const pagingId = yield* getFeedPagingIdActive();

    if (isOverLimit) {
      yield* putPagingFeedRefresh();
    } else {
      yield* putPagingFeedRefresh({ excludePagingId: pagingId });
      yield put({
        type: PAGINATION_UNSHIFT,
        payload: {
          data: [identity],
          pagingId: [pagingId]
        }
      });
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* unpinItem({ payload: { identity } }: ItemLocalAction) {
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, dialogBackend, i18n } =
    yield* getGlobalContext();

  const owner_id = item?.owner_id;

  if (!item) return;

  const config = yield call(getItemActionConfig, item, 'unpinItem');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config?.apiMethod,
      params: {
        owner_id
      }
    });

    const pins = get(response, 'data.data');

    yield call(patchEntity, identity, { pins });

    const ok = yield dialogBackend.confirm({
      message: i18n.formatMessage({ id: 'unpin_reload_confirm_desc' })
    });

    if (ok) {
      window.location.reload();
    }

    // todo: how to handle unpin
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* pinHome({ payload: { identity } }: ItemLocalAction) {
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, getSetting, i18n } = yield* getGlobalContext();
  const limit = parseInt(getSetting('activity.feed.total_pin_in_homepage'));

  if (!item) return;

  const config = yield call(getItemActionConfig, item, 'pinHome');

  if (!config?.apiUrl) return;

  const total = yield* getTotalPins(null);

  const isOverLimit = limit > 0 && total >= limit;
  const configConfirm = isOverLimit
    ? {
        confirm: {
          message: i18n.formatMessage({
            id: 'over_limit_total_pinned_feeds_description'
          })
        }
      }
    : {};

  const ok = yield* handleActionConfirm({ ...config, ...configConfirm });

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config?.apiMethod
    });

    const pins = get(response, 'data.data');

    yield call(patchEntity, identity, { pins });

    yield call(handleActionFeedback, response);

    const pagingId = yield* getFeedPagingIdActive();

    if (isOverLimit) {
      yield* putPagingFeedRefresh();
    } else {
      yield* putPagingFeedRefresh({ excludePagingId: pagingId });
      yield put({
        type: PAGINATION_UNSHIFT,
        payload: {
          data: [identity],
          pagingId: [pagingId]
        }
      });
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* unpinHome({ payload: { identity } }: ItemLocalAction) {
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, dialogBackend, i18n } =
    yield* getGlobalContext();

  if (!item) return;

  const config = yield call(getItemActionConfig, item, 'unpinHome');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config?.apiMethod
    });

    const pins = get(response, 'data.data');

    yield call(patchEntity, identity, { pins });

    const ok = yield dialogBackend.confirm({
      message: i18n.formatMessage({ id: 'unpin_reload_confirm_desc' })
    });

    if (ok) {
      window.location.reload();
    }

    // todo: how to handle unpin
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('pinItem', pinItem),
  takeEvery('unpinItem', unpinItem),
  takeEvery('pinHome', pinHome),
  takeEvery('unpinHome', unpinHome)
];

export default sagas;
