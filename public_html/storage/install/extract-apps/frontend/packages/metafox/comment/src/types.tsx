import {
  ItemExtraShape,
  ItemReactionInformationShape,
  ItemShape,
  ItemViewProps
} from '@metafox/ui';

export interface CommentExtraDataShape {
  extra_id: number;
  extra_type: string;
  comment_id: number;
  item_id: number;
  is_deleted: boolean;
  image: Record<string, any>;
  params?: Record<string, any>;
}

export interface PreviewUploadPhotoHandle {
  attachFiles: (files: FileList) => void;
  clear: () => void;
  checkIsLoading: () => boolean;
}

export interface CommentItemShape extends ItemShape {
  text: string;
  editText?: string;
  parent_id: string;
  has_preview_link: string;
  hideExtraData?: boolean;
  isEditing: boolean;
  isLoading: boolean;
  extra: ItemExtraShape;
  preFetchingComment?: Record<string, any>;
  creation_date: string;
  children?: string[];
  most_reactions_information?: Array<ItemReactionInformationShape>;
  user_reacted?: string;
  extra_data?: CommentExtraDataShape;
  is_edited?: boolean;
  is_hidden?: boolean;
  role_label?: string;
  isNew?: string;
}

export interface CommentItemState {
  menuOpened?: boolean;
  commentOpened?: boolean;
  commentFocused?: number;
  editing?: boolean;
  loading?: boolean;
  replyUser?: Record<string, any>;
}

export interface CommentItemActions {
  openReplyComposer: () => void;
  viewMoreReplies: (
    payload?: Record<string, any>,
    meta?: Record<string, any>
  ) => void;
  viewMoreComments: (
    payload?: Record<string, any>,
    meta?: Record<string, any>
  ) => void;
  removePreviewLink: () => void;
  deleteComment: () => void;
}

export type CommentItemProps = ItemViewProps<
  CommentItemShape,
  CommentItemActions,
  CommentItemState
> & {
  extra_data: CommentExtraDataShape;
  openReplyComposer?: () => void;
  parent_user?: Record<string, any>;
  identityResource?: string;
  showActionMenu?: boolean;
  searchParams: any;
  sortType: any;
};

export type ReplyItemProps = CommentItemProps;

export interface EditContentProps {
  text?: string;
  extra_data?: CommentExtraDataShape;
}

export interface CommentContentProps {
  text?: string;
  extra_data?: CommentExtraDataShape;
  identity?: string;
  handleAction?: any;
  actions?: any;
  isReply?: boolean;
  parent_user?: Record<string, any>;
  isHidden?: boolean;
  isPreviewHidden?: boolean;
}

export interface CommentExtraDataProps {
  extra_data?: CommentExtraDataShape;
}

export interface CommentComposerProps {
  open?: boolean;
  identity: string;
  focus?: boolean;
  text?: string;
  editing?: boolean;
  extra_data?: CommentExtraDataShape;
  margin?: 'dense' | 'normal' | 'none';
  onCancel?: () => void;
  onSuccess?: () => void;
  onFailure?: () => void;
  isReply?: boolean;
  parentUser?: Record<string, any>;
  replyUser?: Record<string, any>;
  actions?: any;
  identityResource?: string;
}

export interface CommentListProps {
  data: string[];
  open?: boolean;
  identity: string;
  total_comment?: number;
  total_reply?: number;
  total_hidden?: number;
  handleAction?: any;
  viewMoreComments?: (
    payload?: Record<string, any>,
    meta?: Record<string, any>
  ) => void;
  parent_user?: Record<string, any>;
  sortType?: SortTypeValue;
  setSortType?: (value: string) => void;
  setLoadingSort?: (value: boolean) => void;
  isDetailPage?: boolean;
  disablePortalSort?: boolean;
  showActionMenu?: boolean;
}

export interface CommentComposerPluginControlProps {
  testid: string;
  icon: string;
  title: string;
  label?: string;
  onClick: () => void;
  disabled?: boolean;
}

export interface CommentComposerPluginProps {
  disabled?: boolean;
  control: React.FC<CommentComposerPluginControlProps>;
  previewRef: any;
  onStickerClick: any;
  editorRef: any;
  onAttachFiles: any;
}

export interface AppState {
  entities: { comment: Record<string, CommentItemShape> };
}

export type SortTypeValue = 'oldest' | 'newest' | 'all' | 'relevant';

export type SortTypeModeValue = 'asc' | 'desc';
