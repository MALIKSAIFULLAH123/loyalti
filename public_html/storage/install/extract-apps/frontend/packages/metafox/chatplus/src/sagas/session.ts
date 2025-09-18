/**
 * @type: saga
 * name: chatplus.session
 */

import {
  APP_BOOTSTRAP_DONE,
  MFOX_API_URL,
  fulfillEntity,
  getGlobalContext,
  getSession
} from '@metafox/framework';
import { put, takeLatest, all, select, call } from 'redux-saga/effects';
import {
  BuddyItemShape,
  ChatplusConfig,
  InitResultShape,
  RoomType
} from '../types';
import { normalizeRoomItem, normalizeSubscriptionItem } from '../utils';
import { getOpenChatRoomsSelector } from '../selectors';

function* bootstrapChatplus({ type }) {
  const { chatplus, cookieBackend, getSetting } = yield* getGlobalContext();
  const { user } = yield* getSession();
  const config = getSetting<ChatplusConfig>('chatplus');

  if (!user?.id) return;

  if (!config || !config?.server) return;

  try {
    config.ddpDebug = true;
    config.debug = true;
    config.accessToken = cookieBackend.get('token');
    config.userId = user.id.toString();
    config.siteUrl =
      process.env.MFOX_ROUTE_BASE_NAME.replace(/(\/+)$/, '') || '';
    config.siteUrlApi =
      MFOX_API_URL.replace(/^\/api\/v1/, '')
        .replace(/^\/api/, '')
        .replace(/(\/+)$/, '') || '';
    config.chatUrl = `${config.server.replace(/(\/+)$/, '')}`;
    config.socketUrl = `${config.server
      .replace(/^http/, 'ws')
      .replace(/(\/+)$/, '')}/websocket`;
    const isReInit = type === 'chatplus/reInit';
    const data: InitResultShape = yield chatplus.init(user, config, isReInit);

    const buddy: Record<string, BuddyItemShape> = {};

    config.Site_Url = data.publicSettings.Site_Url;

    data.rooms.forEach(item => {
      buddy[item._id] = {
        id: item._id,
        name: item.name,
        avatar: '',
        t: item.t
      };
    });

    data.subscriptions.forEach(sub => {
      if (!buddy[sub.rid]) return;

      buddy[sub.rid].name = sub.fname;

      if (sub.t === RoomType.Direct) {
        buddy[sub.rid].username =
          sub.t === RoomType.Direct ? sub.name : `@${sub.fname || sub.name}`;
      }

      if (sub?.other?.avatarETag) {
        buddy[sub.rid].avatarETag = sub.other.avatarETag;
      }
    });

    const entities = {
      chatplus: {
        entities: {
          buddy,
          subscription: data.subscriptions.reduce((acc, x) => {
            normalizeSubscriptionItem(x);
            acc[x.rid] = x;

            return acc;
          }, {}),
          room: data.rooms.reduce((acc, x) => {
            normalizeRoomItem(x);
            acc[x._id] = x;

            return acc;
          }, {})
        }
      }
    };

    yield* fulfillEntity(entities);
    yield put({ type: 'chatplus/init', payload: data });
  } catch (error) {
    //
    // eslint-disable-next-line no-console
    console.log('bootstrapChatplus error', error);
  }
}

function* addListenEventRooms(rid: string) {
  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.listenStreamNotifyRoom(rid);
  } catch (error) {}
}

function* reconnectListenEvent() {
  const { chatplus, getPageParams } = yield* getGlobalContext();

  const openRooms = yield select(getOpenChatRoomsSelector);

  try {
    const { rid } = getPageParams();

    if (rid) {
      yield call(addListenEventRooms, rid);
    }

    yield chatplus.listenStreamNotifyLogged();
    yield chatplus.listenStreamNotifyUser();
    yield chatplus.handleStreamNotify();
    yield all(
      openRooms?.values?.map(room => call(addListenEventRooms, room.rid))
    );
  } catch (err) {}
}

function* clearAlert() {
  const { chatplus } = yield* getGlobalContext();

  try {
    yield put({
      type: 'core/status/fulfill',
      payload: { new_chat_message: 0 }
    });

    yield chatplus.clearAlert();
    // eslint-disable-next-line no-empty
  } catch (error) {}
}

function* clickItem() {
  try {
    yield* clearAlert();
    // eslint-disable-next-line no-empty
  } catch (error) {}
}

const sagas = [
  takeLatest([APP_BOOTSTRAP_DONE, 'chatplus/reInit'], bootstrapChatplus),
  takeLatest('chatplus/reconnectListenEvent', reconnectListenEvent),
  takeLatest('chatplus/status/clearAlert', clearAlert),
  takeLatest('chatplus/menu/clickItem', clickItem)
];

export default sagas;
