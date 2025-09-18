/**
 * @type: saga
 * name: advertise.saga.sponsorshipStatistic
 */

import {
  getGlobalContext,
  getResourceConfig,
  ItemLocalAction,
  patchEntity,
  getSession,
  getItem
} from '@metafox/framework';
import { takeEvery, take } from 'redux-saga/effects';
import { APP_NAME, RESOURCE_SPONSOR } from '../constants';

const TRACKING_IN_VIEW = 'tracking/inViewItem';
const TRACKING_IN_VIEW_EXIT = 'tracking/exitInViewItem';
const ACTION_UPDATE_VIEW = 'advertise/sponsorship/updateView';
const LINK_TRACKING_CLICK = 'link/trackingClick';

function* updateView(action: ItemLocalAction & { payload: any }) {
  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();
  const { loggedIn } = yield* getSession();
  const config = yield* getResourceConfig(
    APP_NAME,
    RESOURCE_SPONSOR,
    'updateTotalView'
  );

  if (!config) return;

  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item || item?._sponsorship_viewed || !config.apiUrl || !loggedIn) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod || 'POST',
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, {
        item_type: item.resource_name,
        item_ids: [item.id]
      })
    });

    const status = response.data?.status;

    if (status === 'success') {
      yield* patchEntity(item._identity, { _sponsorship_viewed: true });
    }
    // eslint-disable-next-line no-empty
  } catch (error) {}
}

function* updateClick(action: ItemLocalAction & { payload: any }) {
  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();
  const { loggedIn } = yield* getSession();
  const config = yield* getResourceConfig(
    APP_NAME,
    RESOURCE_SPONSOR,
    'updateTotalClick'
  );

  if (!config) return;

  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (
    !item ||
    !item?.is_sponsor ||
    item?._sponsorship_clicked ||
    !config.apiUrl ||
    !loggedIn
  )
    return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod || 'POST',
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, {
        item_type: item.resource_name,
        item_ids: [item.id]
      })
    });

    const status = response.data?.status;

    if (status === 'success') {
      yield* patchEntity(item._identity, { _sponsorship_clicked: true });
    }
    // eslint-disable-next-line no-empty
  } catch (error) {}
}

function* inViewItem(action: ItemLocalAction & { payload: any }) {
  const { getSetting, dispatch } = yield* getGlobalContext();
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { loggedIn } = yield* getSession();

  if (!item || !item?.is_sponsor || item?._sponsorship_viewed || !loggedIn)
    return;

  try {
    let timeout;
    // get second delay
    const delay_time_to_count_sponsor_view = getSetting(
      'advertise.delay_time_to_count_sponsor_view',
      0
    );
    const time = delay_time_to_count_sponsor_view * 1000;

    // eslint-disable-next-line prefer-const
    timeout = setTimeout(() => {
      dispatch({
        type: ACTION_UPDATE_VIEW,
        payload: { identity }
      });
    }, time);

    const exitEvent = yield take(`${TRACKING_IN_VIEW_EXIT}/${identity}`);

    const exitInViewItem = exitEvent?.payload?.identity === identity;

    if (exitInViewItem) {
      if (timeout) {
        clearTimeout(timeout);
      }
    }

    // eslint-disable-next-line no-empty
  } catch (error) {}
}

const sagas = [
  takeEvery(ACTION_UPDATE_VIEW, updateView),
  takeEvery(TRACKING_IN_VIEW, inViewItem),
  takeEvery(LINK_TRACKING_CLICK, updateClick)
];

export default sagas;
