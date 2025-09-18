import {
  ItemShape,
  ItemViewProps,
  EmbedItemInFeedItemProps
} from '@metafox/ui';
import { BlockViewProps } from '@metafox/framework';

export interface ForumBreadcrumbShape {}

export interface ForumItemShape extends ItemShape {
  name: string;
  description: string;
  text: string;
  view_id: number;
  is_category: boolean;
  is_closed: boolean;
  thread: any;
  post: any;
  ordering: number;
  sub_forums?: {
    id: number[];
  };
}

export interface ForumPostItemShape {
  thread_id: number;
  thread: ForumThreadShape;
  view_id: number;
  text: string;
  description: string;
  order_id: number;
  attachments: any;
  forum_is_closed: boolean;
  is_approved: boolean;
}

export interface ForumThreadShape extends ItemShape {
  title: string;
  forum_id: number;
  poll_id: number;
  is_announcement: boolean;
  is_closed: boolean;
  is_subscribed: boolean;
  is_approved: boolean;
  breadcrumbs: ForumBreadcrumbShape[];
  order_id: number;
  tags?: string[];
  forum?: string;
  poll?: string;
  item?: any;
  modification_date?: string;
  is_viewed?: boolean;
  is_sticked?: boolean;
  is_wiki?: boolean;
  last_post?: string;
}

export type AppState = {};

export type ThreadItemProps = ItemViewProps<
  ForumThreadShape,
  ForumThreadItemActions
>;

export type ThreadDetailViewProps = ThreadItemProps & BlockViewProps;

export type EmbedThreadItemInFeedItemProps =
  EmbedItemInFeedItemProps<ForumThreadShape>;

export type EmbedPostItemInFeedItemProps =
  EmbedItemInFeedItemProps<ForumPostItemShape>;
export interface ForumThreadItemActions {
  deleteItem: () => void;
  approveItem: () => void;
}
