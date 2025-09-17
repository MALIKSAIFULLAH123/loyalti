import { ItemShape, ItemViewProps } from '@metafox/ui';

export type FollowItemActions = {
  showMutualFriends?: () => void;
};

export type FollowItemShape = ItemShape & {
  item?: string;
};

export type FollowItemProps = ItemViewProps<FollowItemShape, FollowItemActions>;
