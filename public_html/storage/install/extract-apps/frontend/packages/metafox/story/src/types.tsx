import { ItemShape } from '@metafox/ui';
import { UserItemShape } from '@metafox/user';

export type AppState = {
  storyStatus: { muted?: boolean; isEditMuted?: boolean };
};

export interface StoryItemProps extends ItemShape {
  id: string;
  has_seen: boolean;
  image: any;
  video?: string;
  type: 'photo' | 'text' | 'video' | string;
  related_comments?: any[];
  reactions?: any[];
  extra_params?: any;
  duration?: any;
  background?: string;
  in_process?: number | boolean;
  is_owner?: boolean;
  expand_link?: string;
  [key: string]: any;
}

export interface StoryUserProps extends UserItemShape {
  stories?: string[];
  [key: string]: any;
}

export enum PauseStatus {
  No = 0,
  Pause = 1,
  Force = 2
}

export enum TypeSizeLiveVideo {
  LANDSCAPE = 'landscape',
  PORTRAIT = 'portrait'
}

export enum ViewFeedStatus {
  Avatar = '1',
  ThumbnailAndAvatar = '2'
}

export type Area = {
  width: number;
  height: number;
  x: number;
  y: number;
};

export type Point = {
  x: number;
  y: number;
};

export enum ItemInteractionTab {
  Viewers = '1',
  Comments = '2'
}

export interface StoryArchiveProps {
  [key: string]: any;
}

export interface TextItemProps {
  id?: string;
  text?: string;
  fontFamily?: string;
  position?: {
    x: number;
    y: number;
  };
  fontSize?: number;
  textAlign?: string;
  color?: string;
  width?: string;
  rotation?: number;
  scale?: number;
  // created
  isNew?: boolean;
  lexicalState?: string;
  visible?: boolean;
}

export interface SubmitPayload {
  extra: ExtraPayloadType;
  expand_link?: string;
  background_id?: string; // (only type=text)
  lifespan: number;
  privacy: number;
  type: 'photo' | 'text' | 'video' | string;
  duration?: number; // (only type=video)
  file?: {
    temp_file?: number;
    type: 'photo';
  }; // (only type photo or video)
  thumb_file: {
    temp_file?: number;
    type: 'photo';
  };
}

export interface ExtraPayloadType {
  isBrowser: boolean;
  size: {
    height: number;
    width: number;
  };
  storyHeight: number;
  transform: TranformType;
  texts: Array<TextsPayload>;
}

export interface TranformType {
  position: {
    top: number | string; // (ex: 10% | 0),
    left: number | string; // (ex: 10% | 0),
  };
  rotation: number;
  scale: number;
}

export interface TextsPayload {
  color: string;
  fontFamily: string;
  fontSize: number;
  text: string;
  width: string;
  transform: TranformType;
  textAlign?: string;
  id?: string;
}
