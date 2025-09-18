import { HandleAction } from '@metafox/framework';
import { UserItemShape } from '@metafox/user/types';

export interface ChatDate {
  $date: number;
}

export type ChatUserStatus = 'online' | 'offline' | 'busy' | 'away' | string;

export enum ChatVisibilityStatus {
  Public = 'public',
  Friendship = 'friendship'
}

export interface UserShape {
  _id: string;
  username: string;
  name?: string;
  status?: ChatUserStatus | number;
  bio?: string;
  avatarETag?: string;
  lastStatusUpdated?: ChatDate;
  metafoxUserId?: string;
  invisible?: string | number;
}

export type DdpCallback = (
  error?: IMeteorError,
  result?: any,
  isReconnect?: boolean
) => void;

export type DdpSubscribe = (error: IMeteorError, result: any) => void;

// p = private group
// d = direct message (chat 1-n)
// c = public chanel
// u =  only user (not room, subscription created :D)
export type ChatRoomType = 'p' | 'd' | 'c' | 'u';

export enum RoomType {
  Direct = 'd',
  Private = 'p',
  Public = 'c',
  OnlyUser = 'u'
}

export enum UserStatusType {
  Offline = 0,
  Online = 1,
  Away = 2,
  Busy = 3,
  Invisible = 4
}

export type IEventHandler = (eventName: string, args: any[]) => void;

export type TooltipPosition = 'left' | 'right' | 'top' | 'bottom';

export type MsgContentType =
  | 'system'
  | 'groupCallEnded'
  | 'groupCall'
  | 'directChatCallEnded'
  | 'messageEmpty'
  | 'messagePinned'
  | 'messageUnPinned'
  | 'standard'
  | 'messageDeleted';

export interface MsgContentProps {
  message: MsgItemShape;
  tooltipPosition: TooltipPosition;
  createdDate: string;
  isRoomLimited: boolean;
  msgType: string;
  user: UserShape; // session user
  isMessageFilter?: boolean;
  [key: string]: any;
}

export interface ChatMsgPassProps {
  archived: boolean;
  settings: PublicSettingsShape;
  perms?: Record<string, any>;
  isMobile: boolean;
  disableReact: boolean;
  room: RoomItemShape;
  seenUserShow?: boolean;
  tooltipPosition?: TooltipPosition;
  user: UserShape;
  isReadonly?: boolean;
  isRoomOwner?: boolean;
  isRoomLimited?: boolean;
  handleAction?: HandleAction;
  subscription?: SubscriptionItemShape;
}

export type CallType =
  | 'video_call_d'
  | 'audio_call_d'
  | 'video_call_p'
  | 'audio_call_p'
  | 'video_call_c'
  | 'audio_call_c';

export type CallStatus =
  | 'start'
  | 'reject'
  | 'accept'
  | 'join'
  | 'miss'
  | 'leave'
  | 'ringing';

export interface ChatplusConfig {
  debug: boolean;
  ddpDebug: boolean;
  authKey: string;
  userId: string;
  server: string;
  socketUrl: string;
  chatUrl: string;
  siteUrl: string;
  siteUrlApi: string;
  accessToken: string; // use for test only
  resumeToken: string;
  groupMsgInMiliSeconds: number;
  ringtoneUrl: string;
  Site_Url?: string;
}

export interface AuthResultShape {
  id: string;
  token: string;
  tokenExpires: { $date: number };
  type: 'resume';
  waitReConnect?: boolean;
}

export interface SuggestionItemShape {
  value: string;
  label: string;
  img?: string;

  [key: string]: string;
}

export interface IMeteorError {
  error: string;
  errorType: 'Meteor.Error';
  isClientSafe: boolean;
  message: string;
  reason: string;
}

export interface RoomPermissionShape {
  'search-msg': boolean;
  'create-group': boolean;
  'add-members': boolean;
  'show-members': boolean;
  'start-call': boolean;
  'start-video-chat': boolean;
  'leave-room': boolean;
  'hide-room': boolean;
  'edit-notification': boolean;
  'delete-room': boolean;
  'add-user-to-any-c-room': boolean;
  'add-user-to-any-p-room': boolean;
  'add-user-to-joined-room': boolean;
  'archive-room': boolean;
  'set-leader': boolean;
  'set-moderator': boolean;
  'set-owner': boolean;
  'mute-user': boolean;
  'remove-user': boolean;
  'assign-roles': boolean;
  'ban-user': boolean;
  'bulk-create-c': boolean;
  'bulk-register-user': boolean;
  'call-management': boolean;
  'close-livechat-room': boolean;
  'close-others-livechat-room': boolean;
  'create-broadcast': boolean;
  'view-broadcast-member-list': boolean;
  'set-react-when-readonly': boolean;
  'post-readonly': boolean;
  postReadonly?: boolean;
  'set-readonly': boolean;
  'create-c': boolean;
  'create-d': boolean;
  'create-p': boolean;
  'delete-c': boolean;
  'delete-d': boolean;
  'delete-message': boolean;
  'delete-own-message': boolean;
  'force-delete-message': boolean;
  'edit-message': boolean;
  'delete-p': boolean;
  'delete-user': boolean;
  'edit-room': boolean;
  'leave-c': boolean;
  'leave-p': boolean;
  'mention-all': boolean;
  'mention-here': boolean;
  'pin-message': boolean;
  'start-call-c-room': boolean;
  'start-call-d-room': boolean;
  'start-call-p-room': boolean;
  'start-video-chat-c-room': boolean;
  'start-video-chat-d-room': boolean;
  'start-video-chat-p-room': boolean;
}

export interface RoomItemShape {
  id: string;
  module_name: string;
  resource_name: string;
  _id: string;
  t: RoomType;
  name: string;
  fname: string;
  muted?: string[];
  f?: boolean;
  ro?: boolean;
  archived?: boolean;
  sysMsg?: boolean;
  u: UserShape;
  lastMessage: MsgItemShape;
  _updatedAt: ChatDate;
  topic?: string;
  announcement?: string;
  description?: string;
  reactWhenReadOnly?: boolean;
  joinCodeRequired?: boolean;
  userId?: string;
  avatarOrigin?: string;
  avatarETag?: string;
  typing?: any[];
  usersCount?: number;
  uids?: any;
  usernames?: any[];
  isNameChanged?: boolean;
  isBotRoom?: boolean;
}

interface OtherSubscriptionShape {
  _id: string;
  username: string;
  avatarETag?: string;
}

export interface SubscriptionItemShape {
  module_name: string;
  resource_name: string;
  id: string;
  _id: string;
  rid: string;
  open: boolean;
  alert: boolean;
  unread: number;
  userMentions: number;
  groupMentions: number;
  ts: ChatDate;
  name: string;
  fname: string;
  f?: boolean;
  customFields: Record<string, any>;
  t: ChatRoomType;
  u: UserShape;
  ls: ChatDate;
  _updatedAt: ChatDate;
  roles: string[];
  archived?: boolean;
  disableNotifications?: boolean;
  muteGroupMentions?: boolean;
  hideUnreadStatus?: boolean;
  desktopNotifications?: string;
  desktopPrefOrigin?: string;
  audioNotifications?: string;
  desktopNotificationDuration?: number;
  mobilePrefOrigin?: number;
  mobilePushNotifications?: number | string;
  emailNotifications?: number;
  emailPrefOrigin?: number;
  audioNotificationValue?: number;
  other?: OtherSubscriptionShape;
  blocked?: boolean;
  blocker?: boolean;
  metafoxBlocked?: boolean;
  metafoxBlocker?: boolean;
  allowMessageFrom?: 'noone' | string;
}

export interface OpenRoomShape {
  rid: string;
  collapsed: boolean;
}

export interface RoomActions {
  reply: (msg: MsgItemShape) => void;
  quote: (msg: MsgItemShape) => void;
  edit: (msg: MsgItemShape) => void;
  cancelEditing: () => void;
  isReadonly: () => boolean;
  isRoomOwner: () => boolean;
  toggleChatRoom: (evt: any) => void;
  closeChatRoom: (evt: any) => void;
  makeCall: (audioOnly: boolean) => void;
  startVideoChat: (audioOnly: boolean) => void;
  startCall: (evt: any) => void;
  showRoomMembers: (evt: any) => void;
  onLeaveClick: (evt: any) => void;
  onNotificationClick: (evt: any) => void;
  addNewMembers: (evt: any) => void;
  onHideRoomClick: (evt: any) => void;
  favoriteRoom: (evt: any) => void;
  removeFavoriteRoom: (evt: any) => void;
  archiveRoom: (evt: any) => void;
  unarchiveRoom: (evt: any) => void;
  deleteRoom: (evt: any) => void;
  newChatGroup: (evt: any) => void;
  markAsRead: (evt: any) => void;
  markAsUnread: (evt: any) => void;
  starredMessages: (evt: any) => void;
  pinnedMessages: (evt: any) => void;
}

export interface SeenUserShape extends UserShape {
  seenAt: ChatDate;
}

export interface RoomMemberItemShape {
  _id: string;
  username: string;
  name: string;
  status: string;
  roles: string[];
  mute: boolean;
}

export interface SessionUserShape extends UserShape {
  _id: string;
  username: string;
  name: string;
  active: boolean;
  roles: string[];
  type: string;
  joinDefaultChannels: boolean;
  metafoxUserId: string;
  customFields: Record<string, any>;
  statusDefault: ChatUserStatus;
}

export interface ChatBuddyItemShape {
  dockStatus: string;
  searching: boolean;
}

export interface IMethodData {
  name: string;
  id?: string;
  params?: any[];
}

export interface MsgEmbedMetaShape {
  ogTitle: string;
  ogDescription: string;
  ogImage: string;
  oembedThumbnailUrl: string;
  oembedTitle: string;
  oembedDescription: string;
}

export interface MsgEmbedShape {
  ignoreParse: boolean;
  url: string;
  parsedUrl?: { host: string };
  meta: MsgEmbedMetaShape;
}

export interface MsgAttachmentAudioShape {
  audio_url: string;
}

export interface MsgAttachmentShape {
  mentions: any;
  title: string;
  author_real_name: string;
  author_name: string;
  text: string;
  audio_url: string;
  title_link: string;
  description: string;
  attachments: any;
  type: string;
  video_url: string;
  video_type: string;
  video_thumb_url: string;
  layout: string;
  image_url: string;
  image_dimensions?: { width: number; height: number };
}

export interface MsgItemShape {
  id: string;
  _id: string;
  channels?: string[];
  mentions: string[];
  msg: string;
  msgRaw?: string;
  rid: string;
  ts: ChatDate;
  updated: ChatDate;
  _updatedAt: ChatDate;
  u: UserShape;
  t?: string;
  system?: boolean;
  pinned?: boolean;
  role?: string; // available if message type is role changed.
  pinnedAt?: ChatDate;
  groupable?: boolean;
  attachments?: any[];
  reactions?: {
    [key: string]: {
      usernames: string[];
      names: string[];
      updatedAt?: ChatDate;
    };
  };
  starred?: [{ _id: string }];
  callId?: string;
  audioOnly?: boolean;
  file?: any;
  is_alert?: boolean;
  is_group?: boolean;
  is_owner?: boolean;
  urls?: MsgEmbedShape[];
  type: string;
  reports: any;
  seenUser?: SeenUserShape[];
  msgType: string;
  msgContentType: MsgContentType;
  module_name: string;
  resource_name: string;
  _identity: string;
  deleted?: boolean;
  filtered?: boolean;
  hiddenByUserId?: string[];
  owners?: UserShape[];
  total?: number;
  editedBy?: any;
}

export interface MsgSetShape {
  u: UserShape;
  ts: { $date: number }; // date from
  system: boolean;
  groupable: boolean;
  t?: string;
  items: string[];
  is_alert?: boolean;
  is_group?: boolean;
  is_owner?: boolean;
  tooltip_position?: TooltipPosition;
}

export interface MsgGroupShape {
  ts: { $date: number }; // last ts
  t0: number;
  t1: number;
  items: MsgSetShape[];
}

// following fields contain "search" popup field Message Push.
export interface CallPushMessageShape {
  type: 'voip-message';
  callId: string;
  userId: string;
  callType: CallType;
  callStatus: CallStatus;
  incoming: boolean;
  apiUrl: string; // chat.phpfox.us/api/v1/phpfox.call_report
}

// following fields containearch" popup field /phpfox.call_report, type="call_info"
export interface CallInfoShape extends CallPushMessageShape {
  rid?: string;
  audioOnly?: boolean;
  msgId?: number;
  canJoin: boolean;
  url: string; // room full url
  group?: boolean;
  roomName: string;
  createdAt: number; // mili seconds
  parts?: UserShape[];
  displayName: string; // display name string
  subject: string; // full subject string
  avatar: string; // full avatar string
  incoming: boolean;
  ringingStartAt: number;
  ringingLifetime: number;
  localDisplayName?: string;
  localAvatar?: string;
  jwt?: string;
  reason: string;
}

// following fields containearch" popup field /phpfox.call_report, type="room_info"
export interface CallRoomInfoShape extends CallInfoShape {
  domain: string; // jitsi room domain
  noSsl: boolean;
  isEnabledTokenAuth: boolean;
  siteUrl: string; // phpfox server
  expiresAt: number; // ms seconds
}

export interface PublicSettingsShape {
  Favorite_Rooms: boolean;
  FileUpload_MaxFileSize: number;
  Message_AllowDeleting: boolean;
  Message_AllowEditing: boolean;
  Message_AllowPinning: boolean;
  Message_AllowStarring: boolean;
  Metafox_Call_Limit: number;
  Metafox_Enable_Video_Chat: boolean;
  Metafox_Enable_Voice_Call: boolean;
  Metafox_User_Per_Call_Limit: number;
  Threads_enabled: boolean;
  API_Metafox_URL?: string;
  MultipleImageUpload_MaxFileCount?: number;
  MultipleImageUpload_MaxFileSize?: number;
  RoomAvatarUpload_MaxFileSize?: number;
  Website?: string;
  Site_Url?: string;
  Metafox_Ringtone_Sound_Url?: string;
  Metafox_Notification_Sound_Url?: string;
}

export interface UserPreferencesShape {
  language: string;
  clockMode: number;
  audioNotifications: string;
  autoImageLoad: boolean;
  collapseMediaByDefault: boolean;
  convertAsciiEmoji: boolean;
  desktopNotificationDuration: number;
  desktopNotificationRequireInteraction: boolean;
  desktopNotifications: string;
  emailNotificationMode: string;
  enableAutoAway: boolean;
  hideAvatars: boolean;
  hideFlexTab: boolean;
  hideRoles: boolean;
  hideUsernames: boolean;
  idleTimeLimit: number;
  messageViewMode: number;
  mobileNotifications: string;
  muteFocusedConversations: boolean;
  newMessageNotification: string;
  newRoomNotification: string;
  notificationsSoundVolume: number;
  saveMobileBandwidth: boolean;
  sendOnEnter: string;
  sidebarGroupByType: boolean;
  sidebarHideAvatar: boolean;
  sidebarShowDiscussion: boolean;
  sidebarShowFavorites: boolean;
  sidebarShowUnread: boolean;
  sidebarViewMode: string;
  unreadAlert: boolean;
  useEmojis: boolean;
  emailNotifications: string;
  highlights: any[];
  mobilePushNotifications: string;
  roomCounterSidebar: boolean;
  sidebarSortby: string;
  allowMessageFrom?: any;
  disableNotifications?: any;
  muteGroupMentions?: any;
}

export interface DdpSubParams {
  name: string;
  id?: string;
  params?: any[];
  callback?: DdpCallback;
}

export interface DdpMethodParams {
  name: string;
  params: any[];
  id?: string;
  callback?: DdpCallback;
  updatedCallback?: DdpCallback;
}

export interface DdpPromiseParams {
  id?: string;
  name: string;
  params: any[];
}

export interface DdpSubListener {
  match: (eventName: string, collection: string) => boolean;
  callback: IEventHandler;
}

export interface DdpClientContext {
  userId?: string;
  token?: string;
  socketUrl: string;

  loadResumeToken(): string;

  saveResumeToken(token: string): void;

  loadAccessToken(): Promise<string>;
}

export interface PermissionShape {
  _id: string;
  roles: string[];
}

export interface InitResultShape {
  login: SessionUserShape;
  publicSettings: PublicSettingsShape;
  userPreferences: UserPreferencesShape;
  permissions: PermissionShape[];
  subscriptions: SubscriptionItemShape[];
  calls: CallInfoShape[];
  rooms: RoomItemShape[];
  users: UserShape[];
  friends: UserShape[];
  subscription: SubscriptionItemShape;
  room: RoomItemShape;
}

export interface InitPayload {
  login: InitResultShape['login'];
  settings: InitResultShape['publicSettings'];
  permissions: InitResultShape['permissions'];
  userPreferences: InitResultShape['userPreferences'];
  data: {
    call: Record<string, CallInfoShape>;
    subscription: Record<string, SubscriptionItemShape>;
    user: Record<string, UserItemShape>;
    message: Record<string, MsgItemShape>;
    room: Record<string, RoomItemShape>;
  };
}

type MessageSearchProps = {
  id: string;
  slot: number;
  mode?: 'un_search' | 'quote' | string;
  total?: number;
  msgIds?: any[];
  mQuoteId?: any;
  loading: boolean;
};

export interface ChatRoomShape {
  room?: RoomItemShape;
  subscription?: SubscriptionItemShape;
  members: UserShape[];
  groups: Record<string, MsgGroupShape>;
  groupIds: string[];
  msgCount?: number;
  msgNewest?: string;
  oldest?: number;
  newest?: number;
  hasMore?: boolean;
  searching: boolean;
  searchText: string;
  collapsed: boolean;
  pinned: boolean;
  starred: boolean;
  messageFilter?: any;
  addNewMembers?: any;
  resultSearchMembers: UserShape[];
  roomProgress?: any;
  messages?: any;
  textEditor?: string;
  endLoadmoreMessage?: boolean;
  endTopLoadmoreMessage?: boolean;
  msgSearch?: MessageSearchProps;
  searchMessages?: Record<string, ChatRoomShape>;
}

export type ReactMode = 'no_react' | 'edit' | 'reply';

export interface ChatComposerProps {
  rid: string;
  room?: RoomItemShape;
  user?: any;
  msgId?: string;
  focus?: boolean;
  text?: string;
  reactMode?: ReactMode;
  margin?: 'dense' | 'normal';
  onSuccess?: () => void;
  onFailure?: () => void;
  onMarkAsRead?: () => void;
  subscription?: any;
  previewRef?: any;
  isAllPage?: boolean;
}

export interface BuddyItemShape {
  id: string;
  name: string;
  avatar: string;
  msg?: MsgItemShape;
  t: ChatRoomType;
  username: string;
  avatarETag: string;
}

type TypeFileRoom = {
  files: any[];
  count: number;
  total: number;
};

export interface RoomFiles {
  rid: string;
  media: TypeFileRoom;
  other: TypeFileRoom;
}

export interface AppState {
  entities: {
    buddy: Record<string, BuddyItemShape>;
    room: Record<string, RoomItemShape>;
    subscription: Record<string, SubscriptionItemShape>;
    message: Record<string, MsgItemShape>;
    filterMessages: Record<string, MsgItemShape>;
  };
  users: Record<string, UserShape>;
  friends: Record<string, UserShape>;
  userPreferences: UserPreferencesShape;
  settings: PublicSettingsShape;
  openRooms: {
    values: OpenRoomShape[];
    active?: string;
    newChatRoom?: boolean;
    init?: boolean;
    identity?: string;
    closeIconMsg?: boolean;
  };
  closeRooms: {
    identity?: string;
    newChatRoom?: boolean;
  };
  permissions: {
    values: PermissionShape[];
    data: Record<string, any>;
  };
  buddyPanel: {
    collapsed: boolean;
    searching: boolean;
    onlineSearching: boolean;
    searchText: string;
  };
  session: { user: SessionUserShape; connected: boolean };
  calls: Record<string, CallInfoShape>;
  chatRooms: Record<string, ChatRoomShape>;
  roomMembers: Record<string, Record<string, UserShape>>;
  newChatRoom: {
    collapsed?: boolean;
    searching?: boolean;
    searchText?: string;
    results?: { users?: UserShape[]; [key: string]: any };
  };
  roomFiles: Record<string, RoomFiles>;
  spotlight: {
    searchText: string;
    users: UserShape[];
    rooms: RoomItemShape[];
    loading: boolean;
  };
  notifications?: {
    unread?: number;
  };
}

export interface PreviewUploadFileHandle {
  attachFiles: (files: FileList) => void;
  clear?: () => void;
  checkIsLoading?: () => boolean;
}

export interface IPropClickAction {
  identity: string;
  type: 'emoji' | 'actionMenu';
}
