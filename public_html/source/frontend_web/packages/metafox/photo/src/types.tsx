import { AppResource, BlockViewProps } from '@metafox/framework';
import {
  EmbedItemInFeedItemProps,
  ItemShape,
  ItemViewProps,
  ImageMatureState,
  ItemReactionInformationShape
} from '@metafox/ui';
import { SxProps } from '@mui/system';

export type PhotoTagShape = ItemShape & {
  id: number;
  px: number;
  py: number;
  content?: string;
  user?: string;
  extra?: { can_remove_tag_friend?: boolean };
};

export type PhotoSetItemShape = ItemShape & {
  total_photo: number;
  remain_photo: number;
  photos: string[];
};

export type PhotoAlbumItemShape = ItemShape & {
  total_item: number;
  remain_item: number;
  photos: string[];
};

export type PhotoSetProps = ItemViewProps<PhotoSetItemShape>;

export type EmbedPhotoSetInFeedItemProps =
  EmbedItemInFeedItemProps<PhotoSetItemShape> & {
    photos: (PhotoItemShape & { error: number })[];
    setVisible?: (state: boolean) => void;
  };

export type EmbedPhotoAlbumInFeedItemProps =
  EmbedItemInFeedItemProps<PhotoAlbumItemShape> & {
    photos: (PhotoItemShape & { error: number })[];
    setVisible?: (state: boolean) => void;
  };

export type OnAttachFiles = (files: FileList) => void;

export type AttachPhotoButtonProps = {
  classes: any;
  icon?: string;
  title?: string;
  accept?: string;
  multiple?: boolean;
  onAttachFiles?: OnAttachFiles;
  previewRef?: React.MutableRefObject<any>;
};

export type PhotoItemShape = {
  title: string;
  description: string;
  tagged_friends: string[];
  avatar?: string;
  album_id?: string;
  album?: string;
  categories?: any[];
  photo_type?: string;
  mature?: ImageMatureState;
  slug?: string;
} & ItemShape;

export type PhotoViewPropsShape = {
  title: string;
  description: string;
  most_reactions_information?: Array<ItemReactionInformationShape>;
} & ItemShape;

export type AlbumItemShape = ItemShape & {
  title: string;
  name: string;
  description: string;
  group_id?: number;
  mature?: number;
  privacy?: number;
  text?: string;
};

export type PhotoViewProps = ItemViewProps<
  PhotoViewPropsShape,
  PhotoItemActions,
  PhotoItemState
>;

export type PhotoAlbumViewProps = ItemViewProps<
  AlbumItemShape,
  AlbumItemActions,
  AlbumItemState
>;

export type PhotoItemState = {
  menuOpened?: boolean;
};

export type AlbumItemState = {
  menuOpened?: boolean;
};

export type PhotoItemActions = {};

export type AlbumItemActions = {};

export type PhotoSetShape = {
  module_name: string;
  resource_name: string;
  total_photo: number;
  item_id: number;
  feed_item_id: number;
  feed_id: number;
  remain_photo: number;
  photos: string[];
  id: number;
};

export type PhotoItemProps = ItemViewProps<
  PhotoItemShape,
  PhotoItemActions,
  PhotoItemState
>;

export type AlbumItemProps = ItemViewProps<
  AlbumItemShape,
  AlbumItemActions,
  AlbumItemState
>;

export type EmbedPhotoInFeedItemProps =
  EmbedItemInFeedItemProps<PhotoItemShape>;

export type EmbedAlbumInFeedItemProps =
  EmbedItemInFeedItemProps<AlbumItemShape>;

export type PhotoDetailProps = {
  nextUrl?: string;
  prevUrl?: string;
  onNextClick?: () => void;
  onPrevClick?: () => void;
} & PhotoItemProps &
  BlockViewProps;

export type AlbumDetailProps = AlbumItemProps & BlockViewProps;

export type FriendSuggestionColllectionShape = {
  text: string;
  loaded: boolean;
  data: any[];
};

export type AppState = {
  resourceConfig: Record<'photo' | 'photo_category', AppResource>;
};

export type PhotoViewModalProps = {
  prevUrl: string;
  nextUrl: string;
  item: any;
  identity: string;
};

export type MediaViewModalProps = {
  item: any;
  identity: string;
  loading?: boolean;
  photo_set: string;
  photo_album: string;
  photo_id: string;
  media_type: 'photo' | 'video';
  error?: boolean;
};

export type MediaViewDetailProps = {
  item: any;
  identity: string;
  nextUrl: string;
  prevUrl: string;
  mediaType: 'photo' | 'video';
  loading: boolean;
  sx: SxProps;
  shouldPreload: 'next' | 'prev' | '';
  fromResource: 'photo_set' | 'photo_album';
  fromResourceId: number;
};

export type MediaViewPhotoProps = {
  identity: string;
  nextUrl: string;
  prevUrl: string;
  mediaType: 'photo' | 'video';
  onAddPhotoTag: () => void;
  onRemovePhotoTag: () => void;
  onMinimizePhoto: () => void;
};
