/**
 * @type: saga
 * name: saga.chatplus.msgActionMenu
 */
import { LocalAction } from '@metafox/framework';
import { put, select, takeLatest } from 'redux-saga/effects';
import {
  getChatUser,
  getPublicSettings,
  getSubscriptionItem
} from '../selectors';
import {
  MsgItemShape,
  PublicSettingsShape,
  SubscriptionItemShape,
  UserShape
} from '../types';

function* msgActionMenu({
  payload: { item, dependActionName }
}: LocalAction<{ item: MsgItemShape; dependActionName: string }>) {
  const user: UserShape = yield select(getChatUser);
  const isOwner = user._id === item.u._id;
  const isStarred = item.starred?.find(x => x._id === user._id);
  const subscription: SubscriptionItemShape = yield select(
    getSubscriptionItem,
    item.rid
  );
  const settings: PublicSettingsShape = yield select(getPublicSettings);

  if (!subscription) return;

  if (!settings) return;

  const { archived } = subscription;
  const isRoomOwner = true;
  const isReadonly = false;
  const isRoomLimited = false;
  const perms = {
    'force-delete-message': true,
    'delete-message': true,
    'delete-own-message': true
  };
  const isSearch = false;

  // const allowQuote = (isRoomOwner || !readonly);
  const allowQuote = false;
  const allowReply =
    (isRoomOwner || !isReadonly) && !isRoomLimited && !item.filtered;
  const allowStarring = settings.Message_AllowStarring && !item.filtered;
  const allowPinning =
    (settings.Message_AllowPinning || perms['pin-message']) &&
    !isRoomLimited &&
    !item.filtered;
  const allowEdit =
    (perms['edit-message'] || (isOwner && settings['Message_AllowEditing'])) &&
    !isRoomLimited &&
    !item.filtered;
  const canDelete =
    (perms['force-delete-message'] ||
      (settings.Message_AllowDeleting &&
        (perms['delete-message'] ||
          (isOwner && perms['delete-own-message'])))) &&
    !isRoomLimited &&
    !item.filtered;

  const payload = {
    item,
    archived,
    isStarred,
    isOwner,
    isRoomOwner,
    isRoomLimited,
    allowQuote,
    allowReply,
    allowStarring,
    allowEdit,
    allowPinning,
    canDelete,
    settings,
    isSearch,
    perms
  };

  yield put({
    type: dependActionName,
    payload
  });
}

const sagas = [takeLatest('chatplus/msgActionMenu', msgActionMenu)];

export default sagas;
