import { ItemShape, ItemViewProps } from '@metafox/ui';
import { UserItemShape } from '@metafox/user/types';

export interface FriendRequestItemShape extends ItemShape {
  friendship?: number;
  user_name: string;
  full_name: string;
  mutual_friends: { total: number; friends: any[] };
  _identity: string;
  avatar: string;
  react_id: string;
  is_owner?: boolean;
}

export type FriendItemShape = UserItemShape & {
  age?: number;
  birthday?: string;
  birthday_format?: string;
};

export interface FriendListItemShape extends ItemShape {
  name: string;
  id: string;
  identity: string;
  resource_name: string;
  module_name: string;
  total_friend: number;
}

export type FriendItemState = {
  menuOpened: boolean;
};

export type FriendRequestItemState = {
  menuOpened: boolean;
};

export type FriendItemActions = {
  showMutualFriends?: () => void;
  showFriends?: () => void;
  cancelFriendRequest?: () => void;
  acceptFriendRequest?: () => void;
  denyFriendRequest?: () => void;
};

export type FriendSuggestionCollectionShape = {
  text: string;
  loaded: boolean;
  data: any[];
  item_id?: number;
};

export interface FriendRequestActions extends FriendItemActions {}

export interface FriendListActions {}

export type FriendItemProps = ItemViewProps<
  FriendItemShape,
  FriendItemActions,
  FriendItemState
>;

export type FriendListItemActions = {};

export type FriendListItemProps = ItemViewProps<
  FriendListItemShape,
  FriendListItemActions,
  FriendItemState
>;

export type FriendRequestItemProps = ItemViewProps<
  FriendRequestItemShape,
  FriendRequestActions,
  FriendRequestItemState
>;

export interface AppState {
  entities: {
    friend: Record<string, FriendItemShape>;
    friend_request: Record<string, FriendRequestItemShape>;
  };
  suggestions: Record<string, FriendSuggestionCollectionShape>;
}
