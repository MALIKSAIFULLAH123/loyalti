/**
 * @type: saga
 * name: saga.chatplus.chatRoom
 */
import {
  AppResourceAction,
  fulfillEntity,
  getGlobalContext,
  getItem,
  handleActionConfirm,
  ItemLocalAction,
  LocalAction,
  patchEntity
} from '@metafox/framework';
import { isFunction, isNull } from 'lodash';
import { put, select, takeEvery, takeLatest } from 'redux-saga/effects';
import {
  getFriends,
  getGroupChatsSelector,
  getItemRoomSpotlightSelector,
  getOpenChatRooms
} from '../selectors';
import {
  AppState,
  RoomItemShape,
  RoomType,
  SuggestionItemShape
} from '../types';
import { normalizeRoomItem, normalizeSubscriptionItem } from '../utils';
import handleActionErrorChat from './handleActionErrorChat';
import {
  addRoomToChatDock,
  getRoomItem,
  openChatRoomFromDock,
  removeMessagesRoom,
  removeRoomFromChatDock
} from './helpers';

export function* openChatRoom(action: {
  type: string;
  payload: { identity: string; isMobile?: boolean; text?: string };
  meta: { onFinally?: () => void };
}) {
  const { onFinally } = action?.meta || {};

  try {
    const { identity, text } = action.payload;
    const { id } = yield* getItem(identity);
    const { chatplus, navigate, isMobile } = yield* getGlobalContext();

    if (!chatplus.isLoggedIn()) {
      return;
    }

    const response = yield chatplus.waitDdpMethod({
      name: '__createDirectMessage',
      params: ['metafoxUserId', id]
    });

    let rid = undefined;

    if (response?.subscription?.rid) {
      rid = response?.subscription?.rid;
    }

    if (response?.room?._id || response?.room?.id) {
      rid = response?.room?._id || response?.room?.id;
    }

    if (!rid) return;

    const room = yield* getRoomItem(rid);

    if (response?.user) {
      const invisible =
        response?.user.status === 0 &&
        isNull(response?.user?.lastStatusUpdated?.$date);

      yield put({
        type: 'chatplus/users/updateStatus',
        payload: {
          _id: response?.user._id,
          status: response?.user.status,
          invisible,
          lastStatusUpdated: response?.user?.lastStatusUpdated
        }
      });
    }

    // room is not exits
    if (!room) {
      const { subscription, room } = response || {};

      normalizeSubscriptionItem(subscription);
      normalizeRoomItem(room);

      yield* fulfillEntity({
        chatplus: {
          entities: {
            subscription: { [rid]: subscription },
            room: { [rid]: room },
            buddy: {
              [rid]: {
                id: rid,
                name: subscription?.fname,
                t: subscription?.t,
                avatarETag: response?.user?.avatarETag,
                username:
                  subscription?.t === RoomType.Direct
                    ? subscription?.name
                    : `@${subscription?.fname || subscription?.name}`
              }
            }
          }
        }
      });

      yield put({
        type: 'chatplus/room/add',
        payload: {
          subscription,
          room
        }
      });
    }

    yield* addRoomToChatDock(rid, text);

    if (action.payload?.isMobile || isMobile) {
      yield put({
        type: 'chatplus/room/text',
        payload: {
          rid,
          text
        }
      });

      navigate(`/messages/${rid}`);
    }
  } catch (error) {
    yield* handleActionErrorChat(error);
  } finally {
    onFinally && onFinally();
  }
}

export function* openDirectMessage(action: { type: string; payload: string }) {}

export function* openBuddy(
  action: LocalAction<{
    rid: string;
    userId: string;
    subscription_id: string;
  }>
) {
  try {
    const { rid, subscription_id, userId } = action.payload;

    yield* addRoomToChatDock(rid);

    if (subscription_id && rid) {
      yield put({
        type: 'chat/room/openChatRoom',
        payload: { identity: rid }
      });
    } else if (userId && !rid) {
      yield put({ type: 'chatplus/room/openDirectMessage', payload: userId });
    }
  } catch (err) {
    // err
  }
}

export function* openChatRoomFromBuddy(
  action: LocalAction<{
    userId?: string;
    rid: string;
    subscription_id?: string;
  }>
) {
  const { userId = null, rid, subscription_id = null } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  try {
    if (subscription_id && rid) {
      this.openChatRoom(rid);
    } else if (userId && !rid) {
      this.openDirectMessageByUserId(userId);
    } else if (rid) {
      const room = yield select(getItemRoomSpotlightSelector, rid);

      if (!room) return;

      if ('c' !== room.t) {
        //
      } else if (!room.joinCodeRequired) {
        yield chatplus.joinPublicRoom(rid);
      } else {
        // presentPrompt({
        //   title: 'please_enter_join_code',
        //   label: 'join_code',
        //   value: '',
        //   placeholder: 'please_enter_join_code',
        //   onSuccess: (joinCode:string) => this.joinPublicRoom(rid, joinCode)
        // });
      }

      // get rooms and join automatic cally.
      chatplus.openChatRoom(rid);
    }
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}
export function* openDirectMessageByUserId(
  action: LocalAction<{
    rid: string;
    id: string;
  }>
) {
  try {
    const { id, rid } = action.payload;
    const { chatplus } = yield* getGlobalContext();

    const room = yield* getRoomItem(rid);

    if (room) {
      yield chatplus.openChatRoom(rid);

      return;
    }

    yield put({
      type: 'chatplus/room/createChatRoomFromDirectMessage',
      payload: {
        type: 'id',
        identity: id,
        skipPrivacyCheck: false
      }
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* getRoom(
  action: ItemLocalAction<{ rid: string }, { onSuccess: (value) => void }>
) {
  const { rid } = action.payload;

  if (!rid) return null;

  try {
    const room = yield* getRoomItem(rid);

    action?.meta?.onSuccess && action?.meta?.onSuccess(room);
  } catch (error) {
    // err
  }
}

function* markAsRead(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { chatplus } = yield* getGlobalContext();

  try {
    const result = yield chatplus.waitDdpMethod({
      name: 'chatplus/markRead',
      params: [identity]
    });

    if (!result) {
      // handle error
    }
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* changeNameGroup(action: ItemLocalAction) {
  const { dialogBackend } = yield* getGlobalContext();
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  try {
    yield dialogBackend.present({
      component: 'dialog.chatplus.changeNameGroupDialog',
      props: { room }
    });
  } catch (err) {
    // do nothing
  }
}

function* openInMessenger(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { navigate } = yield* getGlobalContext();

  try {
    navigate(`/messages/${room?.id}`);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* markAllRead(action: ItemLocalAction) {
  const { chatplus } = yield* getGlobalContext();

  if (!chatplus.isLoggedIn()) {
    return;
  }

  try {
    const result = yield chatplus.markAllRead();

    if (!result) {
      // handle error
    }
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

export function* toggleChatRoom(
  action: ItemLocalAction<{ identity: string; isMarkRead?: boolean }>
) {
  const { identity: rid, isMarkRead = false } = action.payload;

  try {
    yield put({
      type: 'chatplus/room/togglePanel',
      payload: { identity: rid }
    });
    yield put({ type: 'chatplus/openRooms/togglePanel', payload: rid });

    if (isMarkRead) {
      yield put({
        type: 'chatplus/room/markAsRead',
        payload: { identity: rid }
      });
    }
  } catch (error) {
    // err;
  }
}

function* markAsUnread(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { chatplus } = yield* getGlobalContext();

  try {
    const result = yield chatplus.waitDdpMethod({
      name: 'chatplus/markUnread',
      params: [identity]
    });

    if (!result) {
      // handle error
    }
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* leaveRoom(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { chatplus, i18n } = yield* getGlobalContext();

  const config = {
    confirm: {
      title: i18n.formatMessage({ id: 'leave_group_chat' }),
      message: i18n.formatMessage({ id: 'leave_room_description' })
    }
  };
  const ok = yield* handleActionConfirm(config as AppResourceAction);

  if (!ok) return;

  try {
    const result = yield chatplus.waitDdpMethod({
      name: 'leaveRoom',
      params: [identity, true]
    });

    if (!result) {
      // handle error
    }

    yield* removeRoomFromChatDock(identity);

    yield* removeMessagesRoom(identity);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* createChatRoomFromDirectMessage(action: {
  type: string;
  payload: {
    type: 'email' | 'id' | 'metafoxUserId' | 'username';
    identity: string;
    skipPrivacyCheck?: boolean;
    message?: string;
  };
}) {
  try {
    const { type, identity, skipPrivacyCheck } = action.payload;
    const { chatplus } = yield* getGlobalContext();

    if (!chatplus.isLoggedIn()) {
      return;
    }

    const response = yield chatplus.createDirectMessage(
      type,
      identity,
      skipPrivacyCheck
    );

    const { rid } = response.subscription;

    const room = yield* getRoomItem(rid);

    if (response?.user) {
      yield put({
        type: 'chatplus/users/updateStatus',
        payload: {
          _id: response?.user._id,
          status: response?.user.status,
          invisible: response?.user?.invisible,
          lastStatusUpdated: response?.user?.lastStatusUpdated
        }
      });
    }

    // room is not exits
    if (!room) {
      const { subscription, room } = response;

      normalizeSubscriptionItem(subscription);
      normalizeRoomItem(room);

      yield* fulfillEntity({
        chatplus: {
          entities: {
            subscription: { [subscription.rid]: subscription },
            room: { [room._id]: room },
            buddy: {
              [subscription.rid]: {
                id: subscription.rid,
                name: subscription.fname,
                t: subscription.t,
                avatarETag: response?.user?.avatarETag,
                username:
                  subscription.t === RoomType.Direct
                    ? subscription.name
                    : `@${subscription.fname || subscription.name}`
              }
            }
          }
        }
      });

      yield put({
        type: 'chatplus/room/add',
        payload: {
          subscription,
          room
        }
      });
    }

    yield chatplus.openChatRoom(rid);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* saveChatRoomAndOpen(action: {
  type: string;
  payload: {
    data: any;
  };
}) {
  try {
    const { data } = action.payload;
    const { chatplus } = yield* getGlobalContext();

    if (!chatplus.isLoggedIn()) {
      return;
    }

    let rid = '';

    if (data?.room?._id || data?.room?.id) {
      rid = data.room._id || data.room.id;
    }

    if (data?.subscription?.rid) {
      rid = data.subscription.rid;
    }

    if (!rid) return;

    const room = yield* getRoomItem(rid);

    // room is not exits
    if (!room) {
      const { subscription, room } = data || {};

      normalizeSubscriptionItem(subscription);
      normalizeRoomItem(room);

      const roomId = room?._id || subscription?.rid;
      const roomType = room?.t || subscription?.t;

      yield* fulfillEntity({
        chatplus: {
          entities: {
            subscription: { [roomId]: subscription },
            room: { [roomId]: room },
            buddy: {
              [roomId]: {
                id: roomId,
                name: subscription?.fname,
                t: roomType,
                avatarETag: data?.user?.avatarETag,
                username:
                  roomType === RoomType.Direct
                    ? subscription?.name || data?.user?.name
                    : `@${subscription?.fname || subscription?.name}`
              }
            }
          }
        }
      });
    }
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* presentMembers(
  action: ItemLocalAction<{ rid: string }, { onSuccess: (value) => void }>
) {
  const { rid } = action.payload;
  const { onSuccess } = action.meta;

  const { chatplus } = yield* getGlobalContext();

  try {
    const result = yield chatplus.getRoomMembers(rid, null);

    if (!result) {
      // handle error
    }

    onSuccess && onSuccess(result);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* showPresentMembers(action: ItemLocalAction) {
  const { identity } = action.payload;

  const { chatplus, dialogBackend } = yield* getGlobalContext();

  try {
    const result = yield chatplus.getRoomMembers(identity, null);

    if (!result) {
      // handle error
    }

    yield dialogBackend.present({
      component: 'chatplus.dialog.MembersDialog',
      props: { rid: result.rid, users: result.users }
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* hideRoom(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { chatplus, i18n } = yield* getGlobalContext();

  const config = {
    confirm: {
      title: i18n.formatMessage({ id: 'hide_conversation' }),
      message: i18n.formatMessage({ id: 'hide_room_description' })
    }
  };
  const ok = yield* handleActionConfirm(config as AppResourceAction);

  if (!ok) return;

  try {
    const result = yield chatplus.waitDdpMethod({
      name: 'hideRoom',
      params: [identity, true]
    });

    if (result) {
      yield* removeRoomFromChatDock(identity);
    }
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* blockChat(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { chatplus } = yield* getGlobalContext();

  try {
    const params = [{ rid: identity, blocked: room.userId }];

    const result = yield chatplus.waitDdpMethod({
      name: 'blockUser',
      params
    });

    if (!result) {
      // handle error
    }
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* unblockChat(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { chatplus } = yield* getGlobalContext();

  try {
    const params = [{ rid: identity, blocked: room.userId }];

    const result = yield chatplus.waitDdpMethod({
      name: 'unblockUser',
      params
    });

    if (!result) {
      // handle error
    }
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* unfavoriteRoom(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { chatplus, toastBackend, i18n } = yield* getGlobalContext();

  try {
    const result = yield chatplus.waitDdpMethod({
      name: 'chatplus/setFavorite',
      params: [identity, false]
    });

    if (!result) {
      // handle error
    }

    toastBackend.success(i18n.formatMessage({ id: 'unfavorite_successfully' }));
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* favoriteRoom(action: ItemLocalAction) {
  const { identity } = action.payload;
  const { toastBackend, i18n } = yield* getGlobalContext();
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { chatplus } = yield* getGlobalContext();

  try {
    const result = yield chatplus.waitDdpMethod({
      name: 'chatplus/setFavorite',
      params: [identity, true]
    });

    if (!result) {
      // handle error
    }

    toastBackend.success(i18n.formatMessage({ id: 'favorite_successfully' }));
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* unarchiveRoom(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { chatplus, i18n, toastBackend } = yield* getGlobalContext();

  const config: any = {
    confirm: {
      message: i18n.formatMessage({ id: 'message_unarchive_chat' })
    }
  };

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const result = yield chatplus.waitDdpMethod({
      name: 'unarchiveRoom',
      params: [identity]
    });

    if (!result) {
      // handle error
    }

    toastBackend.success(
      i18n.formatMessage(
        { id: 'action_successfully' },
        { action: 'Un-archive' }
      )
    );
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* archiveRoom(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { chatplus, i18n, toastBackend } = yield* getGlobalContext();

  const config = {
    confirm: {
      title: i18n.formatMessage({ id: 'title_archive_chat' }),
      message: i18n.formatMessage({ id: 'message_archive_chat' })
    }
  };

  const ok = yield* handleActionConfirm(config as AppResourceAction);

  if (!ok) return;

  try {
    const result = yield chatplus.waitDdpMethod({
      name: 'archiveRoom',
      params: [identity]
    });

    if (!result) {
      // handle error
    }

    toastBackend.success(
      i18n.formatMessage({ id: 'action_successfully' }, { action: 'Archive' })
    );
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* presentRoomSettings(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { dialogBackend, i18n } = yield* getGlobalContext();

  try {
    yield dialogBackend.present({
      component: 'dialog.chatplus.EditGroupInfo',
      props: {
        rid: identity,
        room,
        title: i18n.formatMessage({ id: 'room_info' }),
        allowEditing: false
      }
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* editRoomInfo(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { dialogBackend, i18n } = yield* getGlobalContext();

  try {
    yield dialogBackend.present({
      component: 'dialog.chatplus.EditGroupInfo',
      props: {
        rid: identity,
        room,
        title: i18n.formatMessage({ id: 'room_info' }),
        allowEditing: true
      }
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* editRoomSettings(action: {
  type: string;
  payload: {
    identity: string;
    value: any;
  };
  meta?: { onSuccess: () => void; onFailure: () => void };
}) {
  const { i18n, dialogBackend } = yield* getGlobalContext();
  const { identity, value } = action.payload;

  const room = yield* getRoomItem(identity);

  if (!identity || !room) {
    action?.meta?.onFailure && action?.meta?.onFailure();

    yield dialogBackend.alert({
      title: i18n.formatMessage({ id: 'Oops!' }),
      message: i18n.formatMessage({ id: 'error_invalid_room' })
    });

    return;
  }

  const { chatplus } = yield* getGlobalContext();

  try {
    const result = yield chatplus.waitDdpMethod({
      name: 'saveRoomSettings',
      params: [identity, value]
    });

    const roomIdentity = `chatplus.entities.room.${identity}`;

    if (value.roomName && result.result) {
      yield* patchEntity(roomIdentity, {
        isNameChanged: true
      });
    }

    action?.meta?.onSuccess && action?.meta?.onSuccess();
  } catch (error) {
    action?.meta?.onFailure && action?.meta?.onFailure();
    yield* handleActionErrorChat(error);
  }
}

function* editNotificationSettings(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { dialogBackend, chatplus, toastBackend, i18n } =
    yield* getGlobalContext();

  try {
    const value = yield dialogBackend.present({
      component: 'dialog.chatplus.NotificationSettings',
      props: {
        rid: room.id
      }
    });

    if (!value) return;

    yield chatplus.saveSubscriptionPreferences(room.id, value);
    dialogBackend.dismiss();
    toastBackend.success(
      i18n.formatMessage({ id: 'action_successfully' }, { action: 'Save' })
    );
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* addUsers(
  action: LocalAction<
    { rid: string; users: string[]; isAddMember?: boolean },
    {
      onSuccess?: () => void;
    }
  >
) {
  const { rid, users } = action.payload;
  const { onSuccess } = action.meta || {};
  const { chatplus, getPageParams, dialogBackend } = yield* getGlobalContext();
  const pageParam: any = getPageParams();

  try {
    const result = yield chatplus.waitDdpMethod({
      name: 'addUsersToRoom',
      params: [{ rid, users }]
    });

    if (!result) {
      // handle error
    }

    if (pageParam?.rid) {
      dialogBackend.dismiss();
    } else {
      yield put({
        type: 'chatplus/room/closeSearchUser',
        payload: { identity: rid }
      });
    }

    isFunction(onSuccess) && onSuccess();
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* newGroup() {
  const { dialogBackend } = yield* getGlobalContext();

  const friends = yield select(getFriends);

  const listFriend = Object.values(friends)
    ? Object.values(friends)?.slice(0, 4)
    : [];

  yield put({
    type: 'chatplus/newChatRoom/search/updateUsers',
    payload: listFriend
  });

  try {
    yield dialogBackend.present({
      component: 'chatplus.dialog.MultipleConversationPicker',
      props: {}
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* newConversationPage() {
  const { dialogBackend } = yield* getGlobalContext();

  try {
    yield dialogBackend.present({
      component: 'chatplus.dialog.MultipleConversationPicker',
      props: {
        isNewConversation: true
      }
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* createGroup(action: ItemLocalAction) {
  const { identity } = action.payload;
  const { dialogBackend, chatplus } = yield* getGlobalContext();
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const result = yield chatplus.getRoomMembers(identity, null);

  try {
    yield dialogBackend.present({
      component: 'chatplus.dialog.MultipleConversationPicker',
      props: {
        users: result.users
      }
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* addNewMembersPage(action: ItemLocalAction) {
  const { identity } = action.payload;
  const { dialogBackend, chatplus } = yield* getGlobalContext();
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const result = yield chatplus.getRoomMembers(identity, null);

  try {
    yield dialogBackend.present({
      component: 'chatplus.dialog.MultipleConversationPicker',
      props: {
        users: result.users,
        isAddMember: true,
        roomId: room.id
      }
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* clickOpenUserProfile(action: any) {
  const { username } = action.payload;

  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.gotoProfileUrl(username);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* newConversation(
  action: LocalAction<{
    users: SuggestionItemShape[];
    readonly?: boolean;
    nameGroup?: string;
    isPublic?: boolean;
  }>
) {
  const { chatplus, getPageParams } = yield* getGlobalContext();
  const pageParam: any = getPageParams();
  const {
    users = [],
    readonly = false,
    isPublic = false,
    nameGroup = ''
  } = action.payload;

  try {
    const result = yield chatplus.createNewMessage(
      users,
      readonly,
      nameGroup,
      isPublic
    );

    if (!result) return;

    const rid = result?.room?.id || result?.room?._id || result?.rid;

    if (!pageParam?.isAllPageMessages) {
      yield put({
        type: 'chatplus/closePanel',
        payload: { identity: 'NEW_CHAT_ROOM' }
      });
      yield* addRoomToChatDock(rid);
    }
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* setUserStatus(action: LocalAction) {
  const { chatplus } = yield* getGlobalContext();
  const type = action.type;

  if (!type) return;

  const status = type.split(':');

  try {
    if (status[1]) {
      yield chatplus.setUserStatus(status[1]);
    }
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* unmuteUserInRoom(
  action: ItemLocalAction<{ identity: string; userId: string }>
) {
  const { identity, userId } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.unmuteUserInRoom(identity, userId);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* muteUserInRoom(
  action: ItemLocalAction<{ identity: string; userId: string }>
) {
  const { identity, userId } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.muteUserInRoom(identity, userId);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* addRoomOwner(
  action: ItemLocalAction<{ identity: string; userId: string }>
) {
  const { identity, userId } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.addRoomOwner(identity, userId);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* removeRoomOwner(
  action: ItemLocalAction<{ identity: string; userId: string }>
) {
  const { identity, userId } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.removeRoomOwner(identity, userId);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* addRoomModerator(
  action: ItemLocalAction<{ identity: string; userId: string }>
) {
  const { identity, userId } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.addRoomModerator(identity, userId);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* removeRoomModerator(
  action: ItemLocalAction<{ identity: string; userId: string }>
) {
  const { identity, userId } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.removeRoomModerator(identity, userId);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* addRoomLeader(
  action: ItemLocalAction<{ identity: string; userId: string }>
) {
  const { identity, userId } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.addRoomLeader(identity, userId);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* removeRoomLeader(
  action: ItemLocalAction<{ identity: string; userId: string }>
) {
  const { identity, userId } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.removeRoomLeader(identity, userId);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* removeUserFromRoom(
  action: ItemLocalAction<{ identity: string; userId: string }>
) {
  const { identity, userId } = action.payload;
  const { chatplus, i18n } = yield* getGlobalContext();

  const config = {
    confirm: {
      title: i18n.formatMessage({ id: 'remove_from_chat' }),
      message: i18n.formatMessage({ id: 'remove_from_chat_description' }),
      positiveButton: { label: i18n.formatMessage({ id: 'remove' }) }
    }
  };
  const ok = yield* handleActionConfirm(config as AppResourceAction);

  if (!ok) return;

  try {
    yield chatplus.removeUserFromRoom(identity, userId);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* searchUser(
  action: LocalAction<{ query: string; rid: string; excludes: boolean }>
) {
  const { chatplus } = yield* getGlobalContext();
  const { query, excludes = [], rid } = action.payload;

  try {
    const data = yield chatplus.waitDdpMethod({
      name: 'chatplus/spotlight',
      params: [query, excludes, { rooms: false, users: true }]
    });

    yield put({
      type: 'chatplus/room/searchUser/FULFILL',
      payload: { data, rid }
    });
  } catch (err) {
    // do nothing
  }
}

function* soundNotification(action: LocalAction<{ rid: string }>) {
  const { getPageParams } = yield* getGlobalContext();
  const { rid } = action.payload;
  const { values, active }: AppState['openRooms'] = yield select(
    getOpenChatRooms
  );
  const pageParam = getPageParams<any>();

  try {
    const checkOpenRooms =
      !pageParam?.rid &&
      (!values?.length || !values.some(item => item.rid === rid));
    const checkAllMessagePage = pageParam?.rid && (!active || active !== rid);

    // check when tab is not active
    if (
      document &&
      (document.hidden || checkOpenRooms || checkAllMessagePage)
    ) {
      const audioPlayer: HTMLMediaElement = document.querySelector(
        '#chatplusSoundNotification'
      );

      if (audioPlayer) {
        audioPlayer.play();
        audioPlayer.muted = false;
      }
    }
  } catch (err) {
    // err
  }
}

function* getRoomFiles(
  action: LocalAction<{
    rid: string;
    queryParam?: string;
    sort?: any;
    count?: any;
    offset?: any;
    type?: any;
    callback?: any;
  }>
) {
  const { rid, queryParam, sort, count, offset, type, callback } =
    action.payload;
  const { chatplus, dispatch } = yield* getGlobalContext();
  let query = '';

  if (!queryParam) {
    query = yield chatplus.getQueryRoomFiles(type);
  } else {
    query = queryParam;
  }

  yield chatplus.getRoomFiles(
    rid,
    query,
    sort,
    count,
    offset,
    (error, payload) => {
      if (error) {
        // console.log('error');
      } else {
        dispatch({
          type: 'chatplus/getRoomFiles',
          payload: {
            ...payload,
            rid,
            type
          }
        });

        if (callback) {
          callback();
        }
      }
    }
  );
}

function* getRoomMembers(
  action: ItemLocalAction<{ rid: string }, { onSuccess: (values) => void }>
) {
  const { rid } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  if (!rid) return;

  try {
    const result = yield chatplus.getRoomMembers(rid, null);

    if (!result) return;

    action?.meta?.onSuccess && action?.meta?.onSuccess(result.users);
  } catch (error) {
    // error
  }
}

function* editUserPreferences(action: ItemLocalAction) {
  const { dialogBackend } = yield* getGlobalContext();

  try {
    yield dialogBackend.present({
      component: 'chatplus.dialog.EditUserPreferences',
      props: {}
    });
  } catch (error) {
    // err
  }
}

function* saveUserPreferences(action: {
  type: string;
  payload: any;
  meta?: { onSuccess: () => {} };
}) {
  const { chatplus, toastBackend, i18n } = yield* getGlobalContext();
  const payload = action.payload;

  try {
    yield chatplus.saveUserPreferences(payload);

    yield put({
      type: 'chatplus/saveUserPreferences',
      payload
    });

    toastBackend.success(
      i18n.formatMessage({ id: 'action_successfully' }, { action: 'Save' })
    );
    action?.meta?.onSuccess && action?.meta?.onSuccess();
  } catch (err) {
    // do nothing
  }
}

function* presentSeenUsersList(action: { type: string; payload: any }) {
  const { dialogBackend } = yield* getGlobalContext();
  const users = action.payload;

  try {
    yield dialogBackend.present({
      component: 'dialog.chatplus.seenUsersListPopup',
      props: { users }
    });
  } catch (err) {
    // do nothing
  }
}

export function* getFirstRoom(
  action: ItemLocalAction<{ rid?: string }, { onSuccess: (values) => void }>
) {
  try {
    let room: RoomItemShape = null;
    const { directChats, publicGroups, chatBot } = yield select(state =>
      getGroupChatsSelector(state, '', true)
    );

    if (chatBot || directChats.length || publicGroups.length) {
      room = chatBot || directChats[0] || publicGroups[0];
    }

    action?.meta?.onSuccess && action?.meta?.onSuccess(room);

    return room;
  } catch (error) {
    // error

    return null;
  }
}

const sagas = [
  takeLatest(
    'chatplus/buddyPanel/createChatRoomFromDock',
    openChatRoomFromDock
  ),
  takeEvery('chatplus/room/addUsers', addUsers),
  takeEvery('chat/room/openChatRoom', openChatRoom),
  takeEvery('chatplus/room/openBuddy', openBuddy),
  takeEvery('chatplus/room/openChatRoomFromBuddy', openChatRoomFromBuddy),
  takeEvery(
    'chatplus/room/openDirectMessageByUserId',
    openDirectMessageByUserId
  ),
  takeEvery('chatplus/room/markAsRead', markAsRead),
  takeEvery('chatplus/room/changeNameGroup', changeNameGroup),
  takeEvery('chatplus/room/openInMessenger', openInMessenger),
  takeEvery('chatplus/room/markAllRead', markAllRead),
  takeEvery('chatplus/room/markAsUnread', markAsUnread),
  takeEvery('chatplus/room/presentMembers', presentMembers),
  takeEvery('chatplus/room/showPresentMembers', showPresentMembers),
  takeEvery('chatplus/room/hideRoom', hideRoom),
  takeEvery('chatplus/room/blockChat', blockChat),
  takeEvery('chatplus/room/unblockChat', unblockChat),
  takeEvery('chatplus/room/unfavoriteRoom', unfavoriteRoom),
  takeEvery('chatplus/room/favoriteRoom', favoriteRoom),
  takeEvery('chatplus/room/unarchiveRoom', unarchiveRoom),
  takeEvery('chatplus/room/archiveRoom', archiveRoom),
  takeEvery('chatplus/room/editSettings', editRoomSettings), // edit room info
  takeEvery('chatplus/room/editInfoSettings', editRoomInfo), // edit room info
  takeEvery('chatplus/room/presentSettings', presentRoomSettings), // present room info
  takeEvery('chatplus/room/editNotificationSettings', editNotificationSettings),
  takeEvery('chatplus/room/leaveRoom', leaveRoom),
  takeEvery(
    'chatplus/room/createChatRoomFromDirectMessage',
    createChatRoomFromDirectMessage
  ),
  takeEvery('chatplus/room/saveChatRoomAndOpen', saveChatRoomAndOpen),
  takeEvery('chatplus/room/openDirectMessage', openDirectMessage),
  takeEvery('chatplus/room/newGroup', newGroup),
  takeEvery('chatplus/room/newConversationPage', newConversationPage),
  takeEvery('chatplus/room/createGroup', createGroup),
  takeLatest('chatplus/room/toggle', toggleChatRoom),
  takeLatest('chatplus/room/clickOpenUserProfile', clickOpenUserProfile),
  takeLatest('chatplus/chatRoom/newConversation', newConversation),
  takeLatest('chatplus/setUserStatus:online', setUserStatus),
  takeLatest('chatplus/setUserStatus:away', setUserStatus),
  takeLatest('chatplus/setUserStatus:busy', setUserStatus),
  takeLatest('chatplus/setUserStatus:invisible', setUserStatus),
  takeLatest('chatplus/room/unmuteUserInRoom', unmuteUserInRoom),
  takeLatest('chatplus/room/muteUserInRoom', muteUserInRoom),
  takeLatest('chatplus/room/addRoomOwner', addRoomOwner),
  takeLatest('chatplus/room/removeRoomOwner', removeRoomOwner),
  takeLatest('chatplus/room/addRoomModerator', addRoomModerator),
  takeLatest('chatplus/room/removeRoomModerator', removeRoomModerator),
  takeLatest('chatplus/room/addRoomLeader', addRoomLeader),
  takeLatest('chatplus/room/removeRoomLeader', removeRoomLeader),
  takeLatest('chatplus/room/removeUserFromRoom', removeUserFromRoom),
  takeLatest('chatplus/room/searchUser', searchUser),
  takeLatest('chatplus/room/addNewMembersPage', addNewMembersPage),
  takeLatest('chatplus/room/getRoom', getRoom),
  takeLatest('chatplus/room/soundNotification', soundNotification),
  takeLatest('chatplus/room/getRoomFiles', getRoomFiles),
  takeEvery('chatplus/room/getRoomMembers', getRoomMembers),
  takeEvery('chatplus/editUserPreferences', editUserPreferences),
  takeEvery('chatplus/room/saveUserPreferences', saveUserPreferences),
  takeEvery('chatplus/room/presentSeenUsersList', presentSeenUsersList),
  takeEvery('chatplus/room/getFirstRoom', getFirstRoom)
];

export default sagas;
