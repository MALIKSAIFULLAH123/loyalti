/**
 * @type: saga
 * name: chat.saga.bootstrap
 */

import {
  APP_BOOTSTRAP_DONE,
  getGlobalContext,
  getSession,
  IS_ADMINCP
} from '@metafox/framework';
import { takeLatest, put } from 'redux-saga/effects';

function* bootstrap() {
  const { chatBackend, getSetting, getAcl } = yield* getGlobalContext();
  const { user } = yield* getSession();

  const setting: any = getSetting();
  const hasAdminAccess = getAcl('core.admincp.has_admin_access');
  const offlineSetting = getSetting('core.offline');
  const offlineMode = !hasAdminAccess && offlineSetting;

  if (!user?.id) return;

  try {
    if (
      IS_ADMINCP ||
      setting?.chatplus?.server ||
      !setting?.chat ||
      offlineMode
    )
      return;

    yield chatBackend.listenRoomNotify(user.id);

    yield put({ type: 'chat/room/getChatRoomList' });
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log('bootstrapChat error', error);
  }
}

const sagas = [takeLatest(APP_BOOTSTRAP_DONE, bootstrap)];

export default sagas;
