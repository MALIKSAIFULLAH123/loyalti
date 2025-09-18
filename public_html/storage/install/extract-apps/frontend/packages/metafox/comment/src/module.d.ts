import '@metafox/framework/Manager';
import {
  AppState,
  CommentItemActions,
  CommentListProps,
  SortTypeValue
} from './types';

declare module '@metafox/framework/Manager' {
  interface Manager {
    CommentList?: React.FC<CommentListProps>;
    CommentReaction?: React.FC<{}>;
    CommentActButton?: React.FC<{
      minimize?: boolean;
      onlyIcon?: boolean;
      handleAction: any;
      identity: string;
    }>;
    ReplyActButton?: React.FC<{
      minimize?: boolean;
      identity?: string;
      handleAction?: any;
      openReplyComposer?: any;
    }>;
    RemovePreviewActButton: React.FC<{
      actions: CommentItemActions;
      identity: string;
      minimize?: boolean;
    }>;
    ReplyItemView?: React.FC<{
      identity: string;
      openReplyComposer: any;
      parent_user?: Record<string, any>;
    }>;
    CommentItemView?: React.FC<{ identity: string; identityResource?: string }>;
    SortCommentList?: React.FC<{ identity: string }>;
    HistoryEditedCommentButton?: React.FC<{
      identity: string;
    }>;
    useSortComment?: (
      sortDefault?: SortTypeValue
    ) => [
      SortTypeValue,
      (prev: SortTypeValue) => void,
      Boolean,
      (prev: Boolean) => void
    ];
  }

  interface GlobalState {
    comment?: AppState;
  }
}
