import { AppResource } from '@metafox/framework';
import {
  CoverSize,
  ItemShape,
  ItemViewProps,
  MenuItemShape
} from '@metafox/ui';

export type GroupItemShape = ItemShape & {
  title: string;
  reg_method: 0 | 1 | 2;
  reg_name: string;
  is_invited: boolean;
  cover_photo_position: string;
  membership: number;
  cover_photo_id: string;
  cover: Record<CoverSize, string>;
  avatar: Record<CoverSize, string>;
  summary: string;
  full_name?: string;
  location?: string;
  start_time?: string;
  end_time?: string;
  rsvp?: number;
  coordinate?: {
    latitude: number;
    longitude: number;
  };
  extra: {
    can_view_privacy?: boolean;
    can_view_members?: boolean;
    can_unmute_in_group?: boolean;
  };
};

export type ProfileMenuItemProps = {
  classes?: Record<'menuItem' | 'activeMenuItem' | 'menuLink' | string, string>;
} & MenuItemShape;

export type ProfileMenuProps = {
  items?: MenuItemShape[];
  activeTab?: string;
  [key: string]: any;
};

export type UserAvatarsGroupProps = {
  classes?: any;
  data: string[];
  limit?: number;
};

export type ProfileHeaderAvatarProps = {
  alt: string;
  avatar: string;
  canEdit?: boolean;
  editLabel?: string;
  editIcon?: string;
  onEdit?: () => void;
};

export type ProfileHeaderCoverProps = {
  image: string;
  left: number;
  top: number;
  alt: string;
  canEdit?: boolean;
  onEdit?: boolean;
};

export type GroupItemActions = {
  joinGroup: () => void;
  unjoinGroup: () => void;
  acceptInvite: () => void;
  declineInvite: () => void;
};

export type GroupMemberActions = {
  editProfileHeaderAvatar: () => void;
  presentMutualFriends: () => void;
  presentFriends: () => void;
  cancelInvitation: () => void;
};

export type GroupManagerActions = {
  removeReportedPost: () => void;
  keepReportedPost: () => void;
  declinePendingPost: () => void;
  approvePendingPost: () => void;
  declinePendingRequest: () => void;
  approvePendingRequest: () => void;
  unBlockMember: () => void;
  getListReport: () => void;
  unMuteMember: () => void;
  viewMemberQuestions: () => void;
};

export type GroupItemState = {};

export type GroupItemProps = ItemViewProps<
  GroupItemShape,
  GroupItemActions & GroupManagerActions & GroupMemberActions,
  GroupItemState
> & {
  itemActionMenu: Array<Record<string, any>>;
};

export type AppState = {
  ui: any;
  entities: {
    group: Record<string, GroupItemShape>;
  };
  resourceConfig: {
    group: AppResource;
  };
};

export enum TypeQuestion {
  FreeAnswer,
  Select,
  CheckBox
}
