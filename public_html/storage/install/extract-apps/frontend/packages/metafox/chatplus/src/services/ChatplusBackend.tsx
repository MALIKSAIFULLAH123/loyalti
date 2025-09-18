/**
 * @type: service
 * name: chatplus
 */
import { Manager } from '@metafox/framework';
import { UserItemShape } from '@metafox/user';
import { randomId as uniqueId, requireParam } from '@metafox/utils';
import { get, isEmpty, isNull } from 'lodash';
import { CHAT_BUDDY, CHAT_ROOMS, DockStatus } from '../constants';
import { getRoomItemSelector } from '../selectors';
import {
  AuthResultShape,
  CallInfoShape,
  CallRoomInfoShape,
  ChatplusConfig,
  DdpCallback,
  DdpPromiseParams,
  DdpSubParams,
  IEventHandler,
  IMeteorError,
  InitResultShape,
  MsgItemShape,
  PermissionShape,
  PublicSettingsShape,
  RoomItemShape,
  SessionUserShape,
  SubscriptionItemShape,
  SuggestionItemShape,
  UserPreferencesShape,
  UserShape
} from '../types';
import { triggerClick } from '../utils';
import DdpClient from './DdpClient';
import openVoIpCallPopup from './openVoIpCallPopup';

type TConnectStatus =
  | 'none'
  | 'connecting'
  | 'connected'
  | 'connect-failed'
  | 'initializing'
  | 'initialize-failed'
  | 'initialized';

export default class ChatplusBackend {
  /**
   * Manager integration.
   */
  public static readonly configKey: string = 'root.chat';

  /**
   * connect status
   */
  private status: TConnectStatus = 'none';

  /**
   * Handle configuration
   */
  private config: ChatplusConfig;

  /**
   * See manager pattern
   */
  private manager: Manager;

  /**
   * handle ddpClient
   */
  private ddpClient: DdpClient;

  /**
   * @private
   */
  private subscribedRooms: Record<string, boolean> = {};

  private loadedHistory: Record<string, boolean> = {};

  private authResult: AuthResultShape;

  private authError: IMeteorError;

  private subscriptions?: Record<string, SubscriptionItemShape>;

  private rooms?: RoomItemShape[];

  private users?: Record<string, UserShape>;

  private settings?: PublicSettingsShape;

  private userPreferences?: UserPreferencesShape;

  private permissions?: PermissionShape[];

  private login?: SessionUserShape;

  constructor() {
    this.waitDdpMethod = this.waitDdpMethod.bind(this);
  }

  public getConfig(): ChatplusConfig {
    return this.config;
  }

  public bootstrap(manager: Manager) {
    this.manager = manager;
  }

  public isLoggedIn(): boolean {
    return !!this.authResult?.id;
  }

  public getAuthResult(): AuthResultShape {
    return this.authResult;
  }

  public getLogin(): SessionUserShape {
    return this.login;
  }

  public getPublicSettings(): PublicSettingsShape {
    return this.settings;
  }

  public getUsers(): Record<string, UserShape> {
    return this.users;
  }

  public getRoom(rid: string): RoomItemShape {
    const { getState } = this.manager;

    try {
      const room = getRoomItemSelector(getState(), rid);

      return room;
    } catch (err) {
      return null;
      // err
    }
  }
  /**
   * support done to handle in jest.
   */
  public connect(isReInit): Promise<AuthResultShape> {
    const { dispatch } = this.manager;
    const { socketUrl, ddpDebug } = this.config;

    if ('none' === this.status || 'connect-failed' === this.status) {
      requireParam(this.config, 'chatUrl, userId, siteUrl, socketUrl');
      this.ddpClient = new DdpClient(ddpDebug, this.manager);
      const callback = (error: IMeteorError, user: AuthResultShape) => {
        if (error) {
          this.status = 'connect-failed';
          this.authError = error;
        } else {
          this.status = 'connected';
          this.authResult = user;
        }

        if (isReInit) {
          dispatch({
            type: 'chatplus/reconnectListenEvent'
          });
        }
      };

      this.status = 'connecting';

      this.ddpClient.connect(
        {
          socketUrl,
          loadAccessToken: this.loadAccessToken.bind(this),
          loadResumeToken: this.loadResumeToken.bind(this),
          saveResumeToken: this.saveResumeToken.bind(this)
        },
        callback
      );
    }

    return this.waitUntilLogin(4000);
  }
  // check to lock for re-connect.
  public init(
    user: UserItemShape,
    config: ChatplusConfig,
    isReInit: boolean
  ): Promise<InitResultShape> {
    const { dispatch } = this.manager;
    this.config = config;

    if (isReInit) {
      this.status = 'none';
      this.authResult.waitReConnect = true;
      this.subscribedRooms = {};
    }

    if ('initialized' === this.status) {
      // do not initialized again
    } else if ('initializing' === this.status) {
      // do not initialize again
    }

    return this.connect(isReInit)
      .then(() => {
        this.listenStreamNotifyLogged();
        this.listenStreamNotifyUser();
        this.handleStreamNotify();
        this.status = 'initializing';

        return this.waitDdpMethod({
          name: '__init',
          params: []
        });
      })
      .then(data => {
        this.status = 'initialized';
        this.transformInitResult(data);

        data.openRooms = this.getChatRooms();
        const callStorage = localStorage.getItem('chatplus/callId');

        if (callStorage) {
          const [callId, status] = callStorage.split('/');

          if (status === 'openCall' || status === 'start')
            dispatch({
              type: 'chatplus/room/getCallRoom',
              payload: { callId }
            });
        }

        return data;
      });
  }

  private transformInitResult(data: InitResultShape): void {
    this.settings = data.publicSettings;
    this.permissions = data.permissions;
    this.login = data.login;
    this.userPreferences = data.userPreferences;
    this.rooms = data.rooms;
  }

  public getStatus() {
    return this.status;
  }

  private waitUntilLogin(
    timeout: number,
    interval: number = 500
  ): Promise<AuthResultShape> {
    let intervalId: any;
    let retry: number = timeout / interval;

    return new Promise((resolve, reject) => {
      intervalId = setInterval(() => {
        retry = retry - 1;

        if (this.authError) {
          clearInterval(intervalId);
          reject(this.authError);
        } else if (this.authResult && !this.authResult.waitReConnect) {
          clearInterval(intervalId);
          resolve(this.authResult);
        } else if (0 > retry) {
          reject('Connection Timeout');
        }
      }, interval);
    });
  }

  public isOwner(userId: string): boolean {
    return this.authResult?.id === userId;
  }

  public sanitizeRemoteFileUrl(file_url: string): string {
    const { chatUrl } = this.config;
    const { id, token } = this.authResult;

    return `${chatUrl}${file_url}?rc_uid=${id}&rc_token=${token}`;
  }

  public getAvatarUrl(username: string): string {
    const { chatUrl } = this.config;

    return `${chatUrl}/avatar/${username}`;
  }

  public getProfileUrl(username: string): string {
    const { siteUrl } = this.config;

    return `${siteUrl}/${username}`;
  }

  public normalizeUserStatus(value: number | string): string {
    switch (value) {
      case 1:
      case '1':
        return 'online';
      case 2:
      case '2':
        return 'away';
      case 3:
      case '3':
        return 'busy';
      case 0:
      case '0':
      case 4:
      case '4':
        return 'invisible';
      default:
        return '';
    }
  }

  public gotoProfileUrl(username: string) {
    const profileUrl = this.getProfileUrl(username);

    if (profileUrl) {
      // do nothing
      triggerClick(profileUrl);
    }
  }

  public roomUpload(
    files: File[],
    roomId: string,
    text?: string,
    keyProp?: string
  ): Promise<void> {
    const { apiClient, dispatch } = this.manager;
    const { API_Metafox_URL } = this.settings;
    const { token, id } = this.authResult;
    const formData = new FormData();

    const key = keyProp || Date.now();

    dispatch({
      type: 'chatplus/room/addRoomFileProgress',
      payload: {
        rid: roomId,
        key,
        value: {
          count: files.length,
          files
        }
      }
    });

    if (files.length === 1) {
      formData.append('file[]', files[0]);
      formData.append('userId', id);
      formData.append('token', token);
      formData.append('roomId', roomId);
    } else {
      formData.append('userId', id);
      formData.append('token', token);
      formData.append('roomId', roomId);
      for (let i = 0; i < files.length; i++) {
        formData.append('file[]', files[i]);
      }
    }

    if (text) {
      formData.append('msg', text);
    }

    const onUploadProgress = event => {
      const progress = Math.round((event.loaded * 100) / event.total);

      dispatch({
        type: 'chatplus/room/updateRoomFileProgress',
        payload: {
          rid: roomId,
          key,
          progress
        }
      });
      // eslint-disable-next-line no-console
    };

    return apiClient
      .post(`${API_Metafox_URL}/chatplus/rooms/upload/${roomId}`, formData, {
        onUploadProgress
      })
      .then(data => {
        // eslint-disable-next-line
        // console.log(data);
      })
      .then(() => {
        // always executed
        dispatch({
          type: 'chatplus/room/deleteRoomFileProgress',
          payload: {
            rid: roomId,
            key
          }
        });
      })
      .catch(error => {
        dispatch({
          type: 'chatplus/room/deleteRoomFileProgress',
          payload: {
            rid: roomId,
            key
          }
        });
        const errorMsg = get(error, 'response.data.error');
        this.presentError(errorMsg);
      });
  }

  /**
   * load access token from current site?
   */
  public loadAccessToken(): Promise<string> {
    const { accessToken } = this.config;

    return new Promise((resolve, reject) => {
      if (accessToken) {
        resolve(accessToken);
      } else {
        reject('401');
      }
    });
  }

  /**
   * load resume token from site
   */
  public loadResumeToken(): string {
    const { authKey, resumeToken, userId } = this.config;

    if (resumeToken) {
      return resumeToken;
    }

    const cached = this.manager?.cookieBackend.get(authKey);

    if (cached && /:/.test(cached)) {
      const $a = cached.split(':');

      if ($a[0] === userId.toString()) {
        return $a[1];
      }
    }
  }

  public saveResumeToken(token: string): void {
    const { userId, authKey } = this.config;
    this.manager?.cookieBackend.set(authKey, `${userId}:${token}`);
  }

  public spotlight(
    query: string,
    withUsers: boolean,
    withRooms: boolean
  ): Promise<{ users: UserShape[] }> {
    const username = 'jack';
    const excludes = [username];
    const params = { users: withUsers, rooms: withRooms };

    return this.waitDdpMethod({
      name: 'chatplus/spotlight',
      params: [query, excludes, params]
    });
  }

  public typingMessage = (
    rid: string,
    userName: string,
    status: boolean,
    moreInfo: any = {},
    callback?: DdpCallback
  ) => {
    this.ddpClient.useMethod({
      name: 'stream-notify-room',
      params: [`${rid}/typing`, userName, status, moreInfo],
      callback
    });
  };

  public getRoomFiles = (
    rid: string,
    query = {},
    sort: string,
    count: number,
    offset: number,
    callback?: DdpCallback
  ) => {
    this.ddpClient.useMethod({
      name: 'getMedias',
      params: [rid, query, sort, count, offset],
      callback
    });
  };

  public getQueryRoomFiles = (type: string) => {
    let query: any = '';
    switch (type) {
      case 'image':
        query = { typeGroup: 'image' };

        break;
      case 'video':
        query = { typeGroup: 'video' };
        break;
      case 'other':
        query = { typeGroup: { $nin: ['image', 'video'] } };
        break;
      case 'media':
        query = { typeGroup: { $in: ['image', 'video'] } };
        break;
      default:
        break;
    }

    return query;
  };

  public createChatRoom(): void {
    this.manager.dialogBackend.present({
      component: 'chatplus.newChatRoomDialog',
      props: {}
    });
  }

  public joinPublicRoom(rid: string, joinCode: string = null): Promise<void> {
    return this.waitDdpMethod({
      name: 'joinRoom',
      params: [rid, joinCode]
    }).then(() => this.openChatRoom(rid));
  }

  public openChatRoomFromBuddy(
    userId?: string,
    rid: string,
    subscription_id?: string
  ): void {
    const { select } = this.manager;

    if (subscription_id && rid) {
      this.openChatRoom(rid);
    } else if (userId && !rid) {
      this.openDirectMessageByUserId(userId);
    } else if (rid) {
      const room = select<RoomItemShape>(
        (prev: any) =>
          prev.chatplus.rooms[rid] ?? prev.chatplus.spotlight.rooms[rid]
      );

      if (!room) return;

      if ('c' !== room.t) {
        //
      } else if (!room.joinCodeRequired) {
        this.joinPublicRoom(rid);
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
      this.openChatRoom(rid);
    }
  }

  public openDirectMessageByUserId(id: string): void {
    const { dispatch } = this.manager;
    const { id: userId } = this.authResult;
    const rid = [id, userId].sort().join('');

    dispatch({
      type: 'chatplus/room/openDirectMessageByUserId',
      payload: { rid, id }
    });
  }

  public createChatRoomFromDirectMessage(rid: string): void {
    this.getRoomMembers(rid, null).then(result => {
      this.manager.dialogBackend.present({
        component: 'chatplus.newChatGroupDialog',
        props: {
          fillUsers: result.users
        }
      });
    });
  }

  public setUserStatus(status: string): Promise<void> {
    return this.waitDdpMethod({
      name: 'setUserStatus',
      params: [status]
    });
  }

  public openChatRoom(rid: string): void {
    if (!rid) return;

    const { dispatch, getPageParams, navigate } = this.manager;

    const params: any = getPageParams();

    if (params?.isAllPageMessages && navigate) {
      navigate(`/messages/${rid}`);

      return;
    }

    dispatch({
      type: 'chatplus/openRooms/addRoomToChatDock',
      payload: { rid }
    });
  }

  public startNewCall(rid: string, audioOnly: boolean): void {
    const { dispatch } = this.manager;
    const { Metafox_User_Per_Call_Limit, Website } = this.settings;

    try {
      const room = this.getRoom(rid);

      const t = room ? room.t : 'd';

      if (
        'd' !== t &&
        Metafox_User_Per_Call_Limit &&
        0 < Metafox_User_Per_Call_Limit
      ) {
        const onSuccess = (usernames: [string]) => {
          if (usernames.length) {
            this._startNewCall(
              rid,
              audioOnly,
              { usernames, noNotification: true },
              (error: any, payload: any) => {
                if (error) {
                  this.presentError(error);
                } else if (false !== payload.canJoin) {
                  dispatch({ type: 'chatplus/callInfo', payload });
                  openVoIpCallPopup(Website, payload.callId, 'start');
                } else {
                  this.presentAlert({ message: '' });
                }
              }
            );
          }
        };

        this.manager.dialogBackend.present({
          component: 'dialog.DialogSelectRoomMembers',
          props: {
            rid,
            limit: Metafox_User_Per_Call_Limit - 1,
            title: 'call_group_members_start_ringing',
            cancelLabel: 'cancel',
            okLabel: audioOnly ? 'start_call' : 'start_video_call',
            alertMsg: 'can_not_call_with_more_than_value_members',
            onSuccess
          }
        });
      } else {
        this._startNewCall(
          rid,
          audioOnly,
          { noNotification: true },
          (error, payload) => {
            if (error) {
              this.presentAlert({ message: error.reason });
            } else {
              dispatch({ type: 'chatplus/callInfo', payload });
              openVoIpCallPopup(Website, payload.callId, 'start');
            }
          }
        );
      }
    } catch (err) {
      // err
    }
  }

  public callAgainFromMessage(msg: MsgItemShape): void {
    const { rid, audioOnly } = msg;
    this.startNewCall(rid, audioOnly);
  }

  public startVideoChat(rid: string): void {
    this.startNewCall(rid, false);
  }

  public startVoiceCall(rid: string): void {
    this.startNewCall(rid, true);
  }

  public rejectCallFromPopup(callId: string): void {
    this.reportCall(callId, null, 'reject');
    this.setCallStatus(callId, 'end');
  }

  public setCallStatus(callId: string, status: string) {
    if (!callId || !status) {
      // TODO
    }
  }

  public dismissModal() {
    this.manager.dialogBackend.dismiss();
  }
  /**
   * using this method on IncomingCallPopup
   */
  public acceptCallFromPopup(callId: string): void {
    this.joinCallById(callId);
  }

  public rejectCallById(callId: string): void {
    this.reportCall(callId, null, 'reject');
  }

  public joinCallById(callId: string): void {
    const { Website } = this.settings;
    this.reportCall(callId, null, 'join');

    this._getCallInfo(callId)
      .then(payload => {
        if (false !== payload.canJoin) {
          openVoIpCallPopup(Website, callId, 'join');
        } else {
          return Promise.reject('can_not_join_call_room_now');
        }
      })
      .catch(err => this.presentError(err));
  }

  public joinCallFromMessage(msg: MsgItemShape): void {
    this.joinCallById(msg.callId);
  }

  private presentAlert(error: IMeteorError | { message: string }) {
    this.manager.dialogBackend.alert({
      message: error?.message
        ? this.manager.i18n.formatMessage({ id: error.message })
        : ''
    });
  }

  public acceptCall(callId: string): void {
    const { Website } = this.settings;

    this._getCallInfo(callId).then((payload: CallInfoShape) => {
      if (false === payload.canJoin) {
        return Promise.reject('can_not_join_call_room_now');
      }

      this.reportCall(callId, null, 'accept');
      openVoIpCallPopup(Website, callId, 'join');
    });
  }

  public deleteRoom(rid: string): Promise<any> {
    return this.manager.dialogBackend
      .confirm({ title: 'are you sure', message: 'delete_room_description' })
      .then(() =>
        this.waitDdpMethod({
          name: 'eraseRoom',
          params: [rid]
        })
      );
  }

  private presentError(error: any): void {
    this.manager.dialogBackend.alert({
      title: 'Error',
      message: error?.message
        ? this.manager.i18n.formatMessage({ id: error.message })
        : error
    });
  }

  public markMessageAsUnread(msg: MsgItemShape): Promise<any> {
    return this.waitDdpMethod({
      name: 'firstUnreadMessage',
      params: [msg]
    });
  }

  private onUserDeleted(eventName: string, payload: any): void {
    this.manager?.dispatch({
      type: 'chatplus/users/removed',
      payload: { userId: payload[0].userId }
    });
  }

  private onAudioNotification: IEventHandler = (eventName, payload) => {
    payload.forEach(item => {
      if (item.payload.rid) {
        this.manager.dispatch({
          type: 'chatplus/room/soundNotification',
          payload: { rid: item.payload.rid }
        });
      }
    });
  };
  private onFriendsChanged: IEventHandler = (eventName, args) => {
    const [t, payload] = args;

    if (['updated', 'inserted'].includes(t) && payload) {
      this.manager.dispatch({ type: 'chatplus/friends/add', payload });
    } else if ('removed' === t && payload) {
      this.manager.dispatch({ type: 'chatplus/friends/removed', payload });
    }
  };

  private onUserNameChanged: IEventHandler = (eventName, payload) => {
    const [arr] = payload;

    this.manager.dispatch({
      type: 'chatplus/onUserNameChanged',
      payload: arr
    });
  };

  private onUserStatusChanged: IEventHandler = (eventName, payload) => {
    const [arr] = payload;

    const invisible = arr[2] === 0 && isNull(arr[4]?.$date);

    this.manager.dispatch({
      type: 'chatplus/users/updateStatus',
      payload: {
        _id: arr[0],
        status: arr[2],
        lastStatusUpdated: arr[4],
        invisible
      }
    });
  };

  private onUserBioChanged: IEventHandler = (eventName, payload) => {
    const [arr] = payload;

    if (!arr) return;

    this.manager.dispatch({
      type: 'chatplus/users/updateBio',
      payload: arr
    });
  };

  private onUserAvatarChanged: IEventHandler = (eventName, payload) => {
    const [arr] = payload;

    if (!arr) return;

    this.manager.dispatch({
      type: 'chatplus/users/updateAvatar',
      payload: arr
    });
  };

  private onUserNewMessage: IEventHandler = (eventName, payload) => {
    const [arr] = payload;

    if (!arr) return;

    this.manager.dispatch({
      type: 'chatplus/users/openRoomNewMessage',
      payload: arr
    });
  };

  private onNotification: IEventHandler = (eventName, payload) => {
    this.manager.dispatch({
      type: 'chatplus/onNotification',
      payload
    });
  };

  private onUnreadChanged: IEventHandler = (eventName, payload) => {
    const [arr] = payload;

    if (!arr) return;

    this.manager.dispatch({
      type: 'chatplus/notifications/updateUnread',
      payload: arr
    });
  };

  private onCallChanged: IEventHandler = (eventName, payload) => {
    this.manager.dispatch({
      type: 'chatplus/onCallChanged',
      payload
    });
  };

  private onMessages: IEventHandler = (eventName, payload) => {
    this.manager.dispatch({
      type: 'chatplus/RoomMessages',
      payload
    });
  };

  private onStreamRoomMessages: IEventHandler = (eventName, args) => {
    this.manager.dispatch({
      type: 'chatplus/addRoomMessages',
      payload: args
    });
  };

  private onSubscriptionChanged: IEventHandler = (eventName, args) => {
    const [t, payload] = args;

    if (['updated', 'inserted'].includes(t)) {
      this.manager.dispatch({ type: 'chatplus/subscription/upsert', payload });
    } else if (['removed'].includes(t)) {
      this.manager.dispatch({ type: 'chatplus/subscription/remove', payload });
    } else if ('hidden-message'.includes(t)) {
      this.manager.dispatch({
        type: 'chatplus/addRoomMessages',
        payload: [payload]
      });
    }
  };

  private onDeleteMessages: IEventHandler = (eventName, payload) => {
    this.manager.dispatch({
      type: 'chatplus/getDeleteMessage',
      payload
    });
  };

  public presentRoomMembers(rid: string): void {
    this.manager.dialogBackend.present({
      component: 'chatplus.showRoomMemberDialog',
      props: { rid }
    });
  }

  private onRoomsChanged: IEventHandler = (eventName, args) => {
    const [t, payload] = args;

    if (['updated'].includes(t) && payload._id) {
      this.manager.dispatch({ type: 'chatplus/rooms/upsert', payload });
    } else if (['inserted'].includes(t) && payload._id) {
      this.manager.dispatch({ type: 'chatplus/rooms/upsertNew', payload });
    } else if ('removed' === t && payload._id) {
      this.manager.dispatch({ type: 'chatplus/rooms/removed', payload });
    }
  };

  public onRoomTyping = (eventName, args) => {
    const rid = eventName.replace('/typing', '');
    const [username, status, params] = args;
    const { dispatch } = this.manager;
    const payload = {
      rid,
      username,
      status,
      params
    };

    dispatch({ type: 'chatplus/rooms/typing', payload });
  };

  public createSearchUserInRoomToMentions(
    rid: string,
    perms: Record<string, boolean>
  ) {
    // do nothing
  }

  public transformRoom(obj: RoomItemShape) {
    return {
      rid: obj._id,
      _id: obj._id,
      key: obj._id,
      name: obj.fname || obj.name,
      username: `@${obj.fname || obj.name}`,
      buddyType: 'room',
      t: obj.t,
      category: obj.t
    };
  }

  public transformUser = (obj: UserShape) => ({
    rid: [obj._id, this.authResult.id].sort().join(''),
    _id: obj._id,
    user_id: obj._id,
    status: obj.status,
    key: obj._id,
    name: obj.name,
    username: obj.username,
    buddyType: 'user',
    category: 'd'
  });

  public transformSubscription = (obj: SubscriptionItemShape) => ({
    rid: obj.rid,
    _id: obj._id,
    subscription_id: obj._id,
    alert: obj.alert,
    unread: obj.unread,
    open: obj.open,
    typeComp: 'full',
    username: 'd' === obj.t ? obj.name : `@${obj.fname || obj.name}`,
    key: obj._id,
    name: obj.fname || obj.name,
    buddyType: 'subscription',
    t: obj.t,
    category: obj.f ? 'f' : obj.t
  });

  public setLocalItem(key: string, value: string): void {
    const { localStore } = this.manager;
    localStore.set(key, value);
  }

  public getLocalItem = (key: string): any => {
    const { localStore } = this.manager;

    return localStore.get(key);
  };

  public getChatRooms(): any {
    const { id } = this.authResult;
    const data = this.getLocalItem(CHAT_ROOMS);

    return data ? JSON.parse(data)[id] : [];
  }

  public saveChatRooms(data: any): any {
    const { id } = this.authResult;
    const result = { [id]: data };
    this.setLocalItem(CHAT_ROOMS, JSON.stringify(result));

    return data;
  }

  public saveChatBuddy(data: any): any {
    this.setLocalItem(CHAT_BUDDY, JSON.stringify(data));

    return data;
  }

  public getChatBuddy = (): any => {
    const data = this.getLocalItem(CHAT_BUDDY);

    return data ? JSON.parse(data) : { dockStatus: DockStatus.expanded };
  };

  public subscribe(items: DdpSubParams[]): string[] {
    return items.map(item => this.ddpClient.subscribe(item));
  }

  public unsubscribe(id: string[]): void {
    id.map(id => this.ddpClient.unsubscribe(id));
  }

  public addUsersToRoom(rid: string, users: string[]) {
    this.waitDdpMethod({
      name: 'addUsersToRoom',
      params: [{ rid, users }]
    }).catch(this.presentError);
  }

  public createDirectMessage(
    type: 'email' | 'id' | 'metafoxUserId' | 'username',
    identity: string,
    skipPrivacyCheck: boolean = false,
    message: string = null
  ): Promise<any> {
    const { dispatch } = this.manager;

    if (!this.authResult) {
      return Promise.reject('user_not_logged_in');
    }

    return this.waitDdpMethod({
      name: '__createDirectMessage',
      params: [type, identity, skipPrivacyCheck]
    })
      .then(payload => {
        const subscription = payload?.subscription;
        const room = payload?.room;

        const rid = subscription?.rid || room?.rid;
        this.dismissModal();

        dispatch({
          type: 'chatplus/room/saveChatRoomAndOpen',
          payload: {
            data: payload
          }
        });

        dispatch({
          type: 'chatplus/room/add',
          payload: {
            subscription,
            room
          }
        });

        if (rid) {
          this.openChatRoom(rid);
        }

        return Promise.resolve(payload);
      })
      .catch(err => Promise.reject(err));
  }

  public createChannel(
    name: string,
    members: string[],
    readOnly: boolean,
    customField: Record<string, any>,
    extraData: Record<string, any>
  ): Promise<any> {
    const { dispatch } = this.manager;

    return this.waitDdpMethod({
      name: 'createChannel',
      params: [name, members, readOnly, customField, extraData]
    })
      .then(payload => {
        this.dismissModal();

        dispatch({ type: 'chatplus/rooms/upsertNew', payload });

        dispatch({
          type: 'chatplus/room/add',
          payload: {
            room: { ...payload, id: payload.rid }
          }
        });

        if (payload.rid) {
          this.openChatRoom(payload.rid);
        }

        return Promise.resolve(payload);
      })
      .catch(err => Promise.reject(err));
  }

  public createPrivateGroup(
    name: string,
    members: string[],
    readOnly: boolean,
    customField: Record<string, any>,
    extraData: Record<string, any>
  ): Promise<any> {
    const { dispatch } = this.manager;

    return this.waitDdpMethod({
      name: 'createPrivateGroup',
      params: [name, members, readOnly, customField, extraData]
    })
      .then(payload => {
        this.dismissModal();

        dispatch({ type: 'chatplus/rooms/upsertNew', payload });

        dispatch({
          type: 'chatplus/room/add',
          payload: {
            room: { ...payload, id: payload.rid }
          }
        });

        if (payload.rid) {
          this.openChatRoom(payload.rid);
        }

        return Promise.resolve(payload);
      })
      .catch(err => Promise.reject(err));
  }

  public createNewMessage(
    items: SuggestionItemShape[],
    readOnly?: boolean,
    nameGroup?: string,
    isPublic?: boolean
  ): Promise<any> {
    const memberCount = items.length;

    if (!memberCount) {
      // do nothing
    } else if (1 === memberCount) {
      return this.createDirectMessage('username', items[0].value);
    } else if (isPublic) {
      const name = `p-${uniqueId()}`;
      const fname = nameGroup || items.map(x => x.label).join(', ');
      const members = items.map(x => x.value);

      return this.createChannel(
        name,
        members,
        readOnly ?? false,
        {},
        { fname }
      );
    } else {
      const name = `p-${uniqueId()}`;
      const fname = nameGroup || items.map(x => x.label).join(', ');
      const members = items.map(x => x.value);

      return this.createPrivateGroup(
        name,
        members,
        readOnly ?? false,
        {},
        { fname }
      );
    }
  }

  public waitDdpMethod<T = any>({
    name,
    id,
    params
  }: DdpPromiseParams): Promise<T> {
    const ddpClient = this.ddpClient;

    return new Promise<T>((resolve, reject) => {
      try {
        ddpClient.useMethod({
          name,
          id,
          params,
          callback: (err, result) => {
            if (err) {
              reject(err);
            } else {
              resolve(result);
            }
          }
        });
      } catch (err) {
        reject(err);
      }
    });
  }

  public listenStreamNotifyLogged(): void {
    this.subscribe([
      {
        name: 'stream-notify-logged',
        params: ['user-status', false]
      },
      {
        name: 'stream-notify-logged',
        params: ['Users:NameChanged', false]
      },
      {
        name: 'stream-notify-logged',
        params: ['Users:Deleted', false]
      },
      {
        name: 'stream-notify-logged',
        params: ['update-bio', false]
      },
      {
        name: 'stream-notify-logged',
        params: ['update-avatar', false]
      },
      {
        name: 'stream-notify-logged',
        params: ['room-update-avatar', false]
      }
    ]);
  }
  public listenStreamNotifyUser(): void {
    const { id: userId } = this.authResult;

    this.subscribe([
      {
        name: 'stream-notify-user',
        params: [`${userId}/message`, false]
      },
      {
        name: 'stream-notify-user',
        params: [`${userId}/subscriptions-changed`, false]
      },
      {
        name: 'stream-notify-user',
        params: [`${userId}/calls-changed`, false]
      },
      {
        name: 'stream-notify-user',
        params: [`${userId}/notification`, false]
      },
      {
        name: 'stream-notify-user',
        params: [`${userId}/audioNotification`, false]
      },
      {
        name: 'stream-notify-user',
        params: [`${userId}/friends-changed`, false]
      },
      {
        name: 'stream-notify-user',
        params: [`${userId}/rooms-changed`, false]
      },
      {
        name: 'stream-notify-user',
        params: [`${userId}/new-message`, false]
      },
      {
        name: 'stream-notify-user',
        params: [`${userId}/unread-changed`, false]
      }
    ]);
  }
  public unListenStreamNotifyRoom(rid: string): void {
    if (!this.subscribedRooms[rid]) return;

    this.subscribedRooms[rid] = false;
  }

  public listenStreamNotifyRoom(rid: string): void {
    if (this.subscribedRooms[rid]) return;

    this.subscribedRooms[rid] = true;

    this.subscribe([
      {
        name: 'stream-room-messages',
        params: [rid, false]
      },
      {
        name: 'stream-notify-room',
        params: [`${rid}/deleteMessage`, false]
      },
      {
        name: 'stream-notify-room',
        params: [`${rid}/typing`, false]
      }
    ]);
  }
  public listenStreamNotifyCallStatus(callId: string): void {
    this.subscribe([
      {
        name: 'pair-call-status',
        params: [callId, false],
        callback() {
          // do nothing
        }
      }
    ]);
  }

  public handleStreamNotify(): void {
    const { id: userId } = this.authResult;

    this.ddpClient.on([
      {
        match: eventName => /(\w+)\/deleteMessage$/i.test(eventName),
        callback: this.onDeleteMessages
      },
      {
        match: eventName => /(\w+)\/rooms-changed$/i.test(eventName),
        callback: this.onRoomsChanged
      },
      {
        match: eventName => /(\w+)\/typing$/i.test(eventName),
        callback: this.onRoomTyping
      },
      {
        match: (_, collection) => 'stream-room-messages' === collection,
        callback: this.onStreamRoomMessages
      },
      {
        match: eventName => `${userId}/subscriptions-changed` === eventName,
        callback: this.onSubscriptionChanged
      },
      {
        match: eventName => `${userId}/messages` === eventName,
        callback: this.onMessages
      },
      {
        match: eventName => `${userId}/calls-changed` === eventName,
        callback: this.onCallChanged
      },
      {
        match: eventName => `${userId}/notification` === eventName,
        callback: this.onNotification
      },
      {
        match: eventName => `${userId}/audioNotification` === eventName,
        callback: this.onAudioNotification
      },
      {
        match: eventName => `${userId}/friends-changed` === eventName,
        callback: this.onFriendsChanged
      },
      {
        match: eventName => `${userId}/unread-changed` === eventName,
        callback: this.onUnreadChanged
      },
      {
        match: eventName => 'user-status' === eventName,
        callback: this.onUserStatusChanged
      },
      {
        match: eventName => 'Users:Deleted' === eventName,
        callback: this.onUserDeleted
      },
      {
        match: eventName => 'Users:NameChanged' === eventName,
        callback: this.onUserNameChanged
      },
      {
        match: eventName => 'update-bio' === eventName,
        callback: this.onUserBioChanged
      },
      {
        match: eventName => 'update-avatar' === eventName,
        callback: this.onUserAvatarChanged
      },
      {
        match: eventName => `${userId}/new-message` === eventName,
        callback: this.onUserNewMessage
      }
    ]);
  }

  public markAllRead(): void {
    this.ddpClient.useMethod({
      name: 'chatplus/markAllRead',
      params: []
    });
  }

  public presentRoomSettings(
    rid: string,
    allowEditing: Boolean
  ): Promise<void> {
    return this.manager.dialogBackend.present({
      component: 'chatplus.editRoomSettingDialog',
      props: { rid, allowEditing }
    });
  }

  public saveRoomSettings(
    rid: string,
    settings: Record<string, any>
  ): Promise<void> {
    return this.waitDdpMethod({
      name: 'saveRoomSettings',
      params: [rid, settings]
    }).catch(this.presentError);
  }

  public saveSubscriptionPreferences(
    rid: string,
    data: Record<string, any>
  ): Promise<void> {
    return this.waitDdpMethod({
      name: 'chatplus/saveNotificationSettings',
      params: [rid, data]
    }).catch(this.presentError);
  }

  public editUserPreferences() {
    this.manager.dialogBackend.present({
      component: 'chatplus.editUserPreferenceDialog',
      props: {}
    });
  }

  public saveUserPreferences(preferences: Record<string, any>): void {
    this.waitDdpMethod({
      name: 'saveUserPreferences',
      params: [preferences]
    }).catch(this.presentError);
  }

  public pingVoIpCall(callId: string, callback?: DdpCallback): void {
    this.ddpClient.useMethod({
      name: 'metafox/call/ping',
      params: [callId],
      callback
    });
  }

  public reportCall(callId: string, msgId: string, type: string): Promise<any> {
    return this.waitDdpMethod({
      name: 'metafox/call/report',
      params: [callId, type]
    });
  }

  public getCallRoom(callId: string): Promise<CallRoomInfoShape> {
    return this.waitDdpMethod({
      name: 'metafox/call/room',
      params: [callId]
    });
  }

  public getCallNotify(callId: string): Promise<CallRoomInfoShape> {
    return this.waitDdpMethod({
      name: 'metafox/call/notify',
      params: [callId]
    });
  }

  private _getCallInfo(callId: string): Promise<CallInfoShape> {
    return this.waitDdpMethod({
      name: 'metafox/call/info',
      params: [callId]
    });
  }

  public loadRoomHistory(
    rid: string,
    oldest: NumberConstructor,
    limit: number
  ): Promise<any> {
    return this.waitDdpMethod({
      name: 'loadHistory',
      params: [rid, oldest ? { $date: oldest } : null, limit, null]
    });
  }

  public saveNotificationSettings(
    rid: string,
    data: Record<string, any>
  ): Promise<void> {
    return this.waitDdpMethod({
      name: 'chatplus/saveNotificationSettings',
      params: [rid, data]
    });
  }

  public searchRoomMessage(
    rid: string,
    text: string,
    limit: number,
    beforeDate: number
  ): Promise<{ messages: MsgItemShape[] }> {
    return this.waitDdpMethod({
      name: '__searchRoomMessages',
      params: [rid, text, limit, beforeDate]
    });
  }

  public searchUsers(
    x: string,
    limit: number,
    rid: string
  ): Promise<{ users: UserShape[] }> {
    return this.waitDdpMethod({
      name: '__searchUsers',
      params: [x, limit, rid]
    });
  }

  public getRoomMembers(
    rid: string,
    limit: number
  ): Promise<{ users: UserShape[] }> {
    return this.waitDdpMethod({
      name: 'chatplus/getRoomMembers',
      params: [rid, limit]
    });
  }

  private _startNewCall(
    rid: string,
    audioOnly: boolean,
    options: Record<string, any>,
    callback?: DdpCallback
  ): void {
    this.ddpClient.useMethod({
      name: 'metafox/call/start',
      params: [
        {
          rid,
          audioOnly,
          ...options
        }
      ],
      callback
    });
  }

  public muteUserInRoom(rid: string, userId: string): Promise<any> {
    return this.waitDdpMethod({
      name: 'chatplus/muteUserInRoom',
      params: [rid, userId]
    });
  }

  public unmuteUserInRoom(rid: string, userId: string): Promise<any> {
    return this.waitDdpMethod({
      name: 'chatplus/unmuteUserInRoom',
      params: [rid, userId]
    });
  }

  public addRoomOwner(rid: string, userId: string): Promise<any> {
    return this.waitDdpMethod({
      name: 'addRoomOwner',
      params: [rid, userId]
    });
  }

  public removeRoomOwner(rid: string, userId: string): Promise<any> {
    return this.waitDdpMethod({
      name: 'removeRoomOwner',
      params: [rid, userId]
    });
  }
  public removeUserFromRoom(rid: string, userId: string): Promise<any> {
    return this.waitDdpMethod({
      name: 'chatplus/removeUserFromRoom',
      params: [rid, userId]
    });
  }

  public addRoomModerator(rid: string, userId: string): Promise<any> {
    return this.waitDdpMethod({
      name: 'addRoomModerator',
      params: [rid, userId]
    });
  }
  public removeRoomModerator(rid: string, userId: string): Promise<any> {
    return this.waitDdpMethod({
      name: 'removeRoomModerator',
      params: [rid, userId]
    });
  }
  public addRoomLeader(rid: string, userId: string): Promise<any> {
    return this.waitDdpMethod({
      name: 'addRoomLeader',
      params: [rid, userId]
    });
  }
  public removeRoomLeader(rid: string, userId: string): Promise<any> {
    return this.waitDdpMethod({
      name: 'removeRoomLeader',
      params: [rid, userId]
    });
  }
  public presentImageView(evt: any, image: any = {}, images: any = []) {
    if (isEmpty(image)) return;

    this.manager.dialogBackend.present({
      component: 'chatplus.dialog.ImageView',
      props: {
        image,
        images: images.length ? images : [image]
      }
    });
  }
  public clearAlert(): Promise<any> {
    return this.waitDdpMethod({
      name: 'chatplus/clearAlert',
      params: []
    });
  }
}
