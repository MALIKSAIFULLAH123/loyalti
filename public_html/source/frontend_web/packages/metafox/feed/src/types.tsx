import {
  EmbedItemInFeedItemProps,
  ItemEmbedObjectShape,
  ItemReactionInformationShape,
  ItemShape,
  ItemViewProps,
  SimpleUserShape
} from '@metafox/ui';

export type FeedItemShape = {
  info?: string;
  status?: string;
  embed_object?: string;
  status_background?: string;
  most_reactions_information?: Array<ItemReactionInformationShape>;
  related_comments: string[];
  related_comments_statistic?: Record<string, any>;
  tagged_friends?: string[];
  item_type: string;
  user_reacted?: string;
  parent_user?: string;
  item_id: number;
  invisible: true;
  isPreFetchingComment?: boolean;
  preFetchingComment?: Record<string, any>;
  is_hidden?: boolean;
  is_hidden_all_user?: boolean;
  is_hidden_all_owner?: boolean;
  is_snooze_user?: boolean;
  is_snooze_owner?: boolean;
  is_just_hide?: boolean;
  total_friends_tagged?: number;
  text: string;
  like_phrase?: string;
  location?: { address: string; show_map?: boolean };
  description?: string;
  _identity: string;
  pins?: any[];
  is_show_location?: boolean;
  role_label?: string;
} & ItemShape;

export type ManageHiddenItemShape = ItemShape & {
  extra: {
    can_snooze_forever?: boolean;
    can_unsnooze?: boolean;
  };
};

export type FeedItemViewState = {
  menuOpened: boolean;
  commentOpened: boolean;
  commentFocused: number;
};

export type FeedItemActions = {
  viewMoreComments: () => void;
};

export type ManageHiddenItemActions = {};

export type ManageHiddenItemProps = ItemViewProps<
  ManageHiddenItemShape,
  ManageHiddenItemActions
> & {
  isFieldName?: string;
  modification_date?: string;
};

export type FeedItemViewProps = ItemViewProps<
  FeedItemShape,
  FeedItemActions,
  FeedItemViewState
> & {
  tagged_friends?: SimpleUserShape[];
  parent_user?: SimpleUserShape;
  embed_object?: ItemEmbedObjectShape;
  isShowIClose?: boolean;
};

export type EmbedFeedInFeedItemProps = EmbedItemInFeedItemProps<FeedItemShape>;

export type AppState = {
  entities: {
    feed: Record<string, FeedItemShape>;
  };
  paging: Record<string, any>;
};
