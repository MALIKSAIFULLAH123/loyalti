import { AppResource } from '@metafox/framework';
import {
  CoverSize,
  EmbedItemInFeedItemProps,
  ItemShape,
  ItemViewProps,
  MenuItemShape
} from '@metafox/ui';

export interface PagesItemShape extends ItemShape {
  title: string;
  is_admin?: boolean;
  is_owner?: boolean;
  cover: Record<CoverSize, string>;
  avatar: Record<CoverSize, string>;
  cover_photo_id?: string;
  membership?: number;
  summary?: string;
  full_name?: string;
  cover_photo_position?: string;
}

export type PagesManageItemAction = {
  approvePendingPage: () => void;
  declinePendingPage: () => void;
};

export type PagesItemAction = {
  likePage: () => void;
  unlikePage: () => void;
  likePage: () => void;
  unlikePage: () => void;
  approvePendingPage: () => void;
  declinePendingPage: () => void;
  acceptInvite: () => void;
  declineInvite: () => void;
  presentMutualFriends: () => void;
  unblockMember: () => void;
  cancelInvitation: () => void;
};

type PagesAction = PagesItemAction & PagesManageItemAction;

export type PagesItemProps = ItemViewProps<PagesItemShape, PagesAction>;

export type PagesItemState = {
  menuOpened?: boolean;
};

export type EmbedPagesInFeedItemProps =
  EmbedItemInFeedItemProps<PagesItemShape>;

export type AppState = {
  ui: any;
  entities: {
    pages: Record<string, PagesItemShape>;
  };
  resourceConfig: {
    page: AppResource;
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
