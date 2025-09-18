import { BlockViewProps } from '@metafox/framework';
import {
  EmbedItemInFeedItemProps,
  ItemShape,
  ItemViewProps,
  ItemExtraShape,
  ItemReactionShape,
  ItemStatisticShape,
  ItemReactionInformationShape
} from '@metafox/ui';

type WebcamConfig = {
  video?: string;
  audio?: string;
};
export interface LivestreamItemShape extends ItemShape {
  title: string;
  description: string;
  location: Record<string, any>;
  categories: any;
  text: string;
  attach_photos: string[];
  tags: string[];
  is_sold: boolean;
  thumbnail_url: string;
  is_streaming?: boolean | number;
  stream_key: string;
  video_url: string;
  is_owner: boolean;
  is_landscape?: boolean;
  webcamConfig?: WebcamConfig;
  extra: ItemExtraShape & {
    can_invite?: boolean;
    can_payment?: boolean;
    can_message?: boolean;
  };
}

export interface LivestreamImageItemShape extends ItemShape {
  image: Record<string, any>;
}

export type LivestreamItemActions = {
  updateViewer: () => void;
  removeViewer: () => void;
};

export type LivestreamItemState = {
  menuOpened?: boolean;
};

export type LivestreamItemProps = ItemViewProps<
  LivestreamItemShape,
  LivestreamItemActions,
  LivestreamItemState
> & {
  categories: any;
};

export type EmbedLivestreamInFeedItemProps =
  EmbedItemInFeedItemProps<LivestreamItemShape>;

export type LivestreamDetailViewProps = LivestreamItemProps &
  BlockViewProps & {
    isModalView?: boolean;
  };
export interface CommentContentProps {
  text?: string;
  extra_data?: Record<string, any>;
  identity?: string;
  handleAction?: any;
  actions?: any;
  isReply?: boolean;
  parent_user?: Record<string, any>;
  isHidden?: boolean;
  isPreviewHidden?: boolean;
}

export interface LiveReactType {
  id?: string;
  module_name?: string;
  reaction?: ItemReactionShape;
}

export interface ReactionLiveType {
  like?: Array<LiveReactType>;
  total_like?: number;
  most_reactions_information?: Array<ItemReactionInformationShape>;
  statistic: ItemStatisticShape;
  resource_name?: string;
  module_namec?: string;
}
export type DdpCallback = (
  error?: IMeteorError,
  result?: any,
  isReconnect?: boolean
) => void;

export interface IMeteorError {
  error: string;
  errorType: 'Meteor.Error';
  isClientSafe: boolean;
  message: string;
  reason: string;
}

export interface DdpMethodParams {
  name: string;
  params: any[];
  id?: string;
  callback?: DdpCallback;
  updatedCallback?: DdpCallback;
}

export interface DdpSubListener {
  match: (eventName: string, collection: string) => boolean;
  callback: IEventHandler;
}

export type IEventHandler = (eventName: string, args: any[]) => void;

export interface DdpSubParams {
  name: string;
  id?: string;
  params?: any[];
  callback?: DdpCallback;
}

export type DdpSubscribe = (error: IMeteorError, result: any) => void;

export interface LivestreamConfig {
  debug: boolean;
  ddpDebug: boolean;
  authKey: string;
  userId: string;
  socketUrl: string;
  accessToken: string; // use for test only
  resumeToken: string;
  groupMsgInMiliSeconds: number;
  ringtoneUrl: string;
  Site_Url?: string;
  per_time_recorder_webcam?: number;
}

export interface DdpPromiseParams {
  id?: string;
  name: string;
  params: any[];
}

export interface AuthResultShape {
  id: string;
  token: string;
  tokenExpires: { $date: number };
  type: 'resume';
  waitReConnect?: boolean;
}

export interface InitResultShape {
  login: SessionUserShape;
}

export interface SessionUserShape {
  _id: string;
  username: string;
  name: string;
  active: boolean;
  roles: string[];
  type: string;
  metafoxUserId: string;
  customFields: Record<string, any>;
}
