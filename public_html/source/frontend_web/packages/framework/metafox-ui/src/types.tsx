import type {
  GridItemProps,
  HandleAction,
  RemoteDataSource
} from '@metafox/framework/types';
import { ThemeId } from '@metafox/layout';
import {
  ButtonProps,
  GridProps,
  IconButtonProps,
  PopperProps,
  SxProps,
  TypographyProps,
  TypographyVariant
} from '@mui/material';
import { DataGridProps } from '@mui/x-data-grid';
import { LinkProps } from 'react-router-dom';

export type GridColumns = DataGridProps['columns'];

export type ItemItemPropsShape = Record<string, any>;

export type ItemStatisticShape = {
  total_like: number;
  total_comment: number;
  total_score: number;
  total_rating: number;
  [key: string]: number;
  total_item: number;
  total_photo: number;
};

export interface FeedEmbedCardProps<A = any> {
  variant?: 'grid' | 'list' | 'default';
  bottomSpacing?: 'normal' | 'dense';
  children?: React.ReactNode;
  link?: string;
  item: A;
  feed: any;
  isShared?: boolean;
  sxOuter?: SxProps;
  sx?: SxProps;
}

export interface FeedEmbedCardMediaProps {
  image?: string;
  widthImage?: string;
  heightImage?: string;
  mediaRatio?: ImageRatio;
  variant?: 'grid' | 'list' | 'default';
  link?: string;
  playerOverlay?: boolean;
  playerOverlayProps?: TImagePlayerOverlay;
  host?: string;
}

export type TLineIconVariant = 'listItemIcon' | 'itemActionIcon';

export interface LineIconProps {
  icon: string;
  component?: React.ElementType;
  className?: string;
  variant?: TLineIconVariant;
  color?:
    | 'primary'
    | 'secondary'
    | 'textPrimary'
    | 'textSecondary'
    | 'inherit'
    | string;
  onClick?: (e?: any) => void;
  style?: any;
  role?: string;
  sx?: SxProps;
}

export type ItemExtraShape = {
  can_view: boolean;
  can_like: boolean;
  can_share: boolean;
  can_delete: boolean;
  can_delete_own: boolean;
  can_download: boolean;
  can_report: boolean;
  can_add: boolean;
  can_edit: boolean;
  can_comment: boolean;
  can_feature: boolean;
  can_approve: boolean;
  can_sponsor: boolean;
  can_sponsor_in_feed: boolean;
  can_purchase_sponsor: boolean;
  can_unlike: boolean;
  can_start_discussion: boolean;
  can_change_privacy_from_feed?: boolean;
  can_edit_cover_photo?: boolean;
  can_message?: boolean;
  can_upload_media?: boolean;
  can_add_cover?: boolean;
  can_remove_link_preview?: boolean;
  can_close?: boolean;
  can_show_payment_button?: boolean;
  can_show_no_payment_gateway_message?: boolean;
  can_view_comment?: boolean;
  can_show_sponsor_label?: boolean;
};

export type BackgroundSize = '48' | '300' | '1024' | 'origin';

export type AvatarSize = '50' | '120' | '200' | 'origin';

export type CoverSize =
  | '75'
  | '100'
  | '150'
  | '240'
  | '500'
  | '1024'
  | 'origin';

export interface ItemUserShape {
  resource_name: string;
  module_name: string;
  full_name: string;
  avatar: Record<AvatarSize, string>;
  link: string;
  is_featured: boolean;
  friend_id: number;
  user_name: string;
  id: number;
  cover: Record<CoverSize, string>;
  statistic: ItemStatisticShape;
  extra?: ItemExtraShape;
  city_location?: string;
  joined?: string;
}

export type ItemShape = {
  id: number | string;
  module_id: string;
  resource_name: string;
  is_featured: boolean;
  is_sponsor: boolean;
  is_sponsored_feed: boolean;
  is_liked: boolean;
  is_pending: boolean;
  is_online: boolean;
  item_id: number;
  creation_date: string;
  image?: Record<string, string>;
  images?: Record<string, string>[];
  extra?: ItemExtraShape;
  user: string;
  owner: string;
  price?: string;
  text_parsed?: string;
  statistic?: ItemStatisticShape;
  link?: string;
  url?: string;
  currency_id?: string;
  short_description?: string;
  description?: string;
  country?: string;
  city?: string;
  province?: string;
  privacy?: number;
  privacy_feed?: {
    icon: string;
    tooltip: string;
    label?: string;
  };
  privacy_detail?: {
    icon: string;
    tooltip: string;
    label?: string;
  };
  summary?: string;
  weblink?: string;
  attachments: string[];
  _identity: string;
};

export type SimpleUserShape = {
  id: number;
  user_name: string;
  full_name: string;
  avatar: string;
  friendship?: number;
  module_name?: string;
  link?: string;
};

export type ItemViewProps<T, U = unknown, S = unknown> = {
  identity: string;
  item: T;
  user?: SimpleUserShape & ItemUserShape;
  itemProps: ItemItemPropsShape;
  actions: U;
  state: S;
  setState: (cb: (prev: S) => S) => void;
  handleAction: (type: string, payload?: unknown) => void;
  wrapAs: React.FC<GridItemProps>;
  wrapProps: GridItemProps;
  pagingId?: string;
};

export type DetailViewProps<T, U = unknown, S = unknown> = {
  identity: string;
  item: T;
  user?: SimpleUserShape;
  actions: U;
  state: S;
  setState: (cb: (prev: S) => S) => void;
  handleAction: (type: string, payload?: unknown) => void;
};

export interface EmbedObjectShape {
  id: number | string;
  module_id: string;
  resource_name?: string;
  module_name?: string;
  is_featured: boolean;
  is_sponsor: boolean;
  is_liked: boolean;
  is_pending: boolean;
  item_id: number;
  creation_date: string;
  image?: string | Record<string, string>;
  images?: Record<string, string>;
  statistic: ItemStatisticShape;
  extra: ItemExtraShape;
  user: ItemUserShape;
  price?: string;
  link?: string;
  currency_id?: string;
  short_description?: string;
  country?: string;
  city?: string;
  province?: string;
  summary?: string;
  title?: string;
  description?: string;
}

export type EmbedItemInFeedItemProps<A = any> = {
  item: A;
  feed: any;
  isShared?: boolean;
  itemView?: string;
  photo: Record<string, any>;
};

export interface FeedEmbedObjectViewProps<
  A extends EmbedObjectShape = EmbedObjectShape,
  B = ItemShape
> {
  embed: A;
  feed: B;
  itemView?: string;
}

export type ItemEmbedObjectShape = ItemShape;

export interface UIBlockProps {
  variant?: string;
  noHeader?: boolean;
  noFooter?: boolean;
  [key: string]: any;
}

export interface UIGridContainerProps extends GridProps {}

export interface UIGridItemProps extends GridProps {}

export interface ItemTabProps {
  id?: string;
  tab: string;
  label: string;
  showWhen?: Record<string, any>;
  statisticKey?: string;
  [key: string]: any;
}

export interface UIBlockViewProps<A = RemoteDataSource, B = UIBlockProps> {
  title?: string;
  themeId?: ThemeId;
  tabProps?: {
    tabs: ItemTabProps[];
    activeTab: string;
    disableGutter: boolean;
    tabsNoSearchBox?: string[];
    placeholderSearch?: string;
    to?: string;
  };
  elements: any;
  dataSource?: A;
  blockView?: string;
  blockHeader?: string;
  blockFooter?: string;
  blockContent?: string;
  blockProps?: B;
}

export type ItemActionMenuProps = {
  id?: string;
  testid?: string;
  label?: string;
  identity?: string;
  menuName?: string;
  dependName?: string;
  appName?: string;
  icon?: string;
  keepMounted?: boolean;
  disableRipple?: boolean;
  className?: string;
  iconClassName?: string;
  children?: React.ReactNode;
  handleAction?: HandleAction;
  size?: ButtonProps['size'];
  color?: IconButtonProps['color'];
  variant?: IconButtonProps['variant'];
  // state?: any; // remove state pass from items.
  control?: JSX.Element;
  scrollRef?: React.RefObject<HTMLDivElement>;
  scrollClose?: boolean;
  placement?: PopperProps['placement'];
  items?: MenuItemShape[];
  disablePortal?: boolean;
  prefixName?: string;
  fallbackName?: string;
  tooltipTitle?: string;
  preventDefault?: boolean;
  sx?: SxProps;
  autoHide?: boolean;
  preventClose?: boolean;
  zIndex?: number;
  popperOptions?: any;
  popperStyles?: Record<string, any>;
  menuStyles?: Record<string, any>;
  variantPopper?: 'hidden-outview' | string;
  triggerOpen?: any;
};

export type MenuItemValue = string;

export interface MenuItemShape {
  className?: string;
  as?: 'link' | 'button' | 'divider' | 'popover' | 'heading' | string;
  note?: string;
  role?: string;
  target?: '_blank' | '_self' | '_parent' | '_top' | string;
  rel?: 'string';
  image?: string;
  dense?: boolean;
  alt?: string;
  active?: boolean;
  onClick?(): void;
  icon?: string;
  iconSecondary?: string;
  label?: string;
  labelSub?: string;
  value?: MenuItemValue;
  params?: Record<string, any>;
  showWhen?: any;
  enabledWhen?: any;
  variant?: 'text' | 'contained' | 'outlined';
  color?: string;
  disabled?: boolean;
  iconVariant?: any;
  testid?: string;
  to?: string;
  asModal?: boolean;
  asChildPage?: boolean;
  keepScroll?: boolean;
  item_name?: string;
  name?: string;
  buttonProps?: Record<string, any>;
  dividerProps?: {
    variant?: 'inset' | 'fullWidth' | 'middle';
    light?: boolean;
    className?: string;
  };
  placement?: PopperProps['placement'];
  items?: MenuItemShape[];
  order_by?: string;
  content?: {
    component: string | React.ElementType;
    props?: Record<string, any>;
  };
  tab?: string;
  preventClose?: boolean;
  dataSource?: { apiUrl?: string; apiParams?: string };
  behavior?: 'more' | 'last' | 'close';
  disablePortal?: boolean;
  menuItemType?: string;
  sx?: SxProps;
}

export interface MenuItemViewProps {
  item: MenuItemShape;
  classes: Record<string, any>;
  handleAction?: HandleAction;
  pathname?: string;
  active?: boolean;
  handleCheckActiveMenu?: (url: string, path: string) => boolean;
  minimize?: boolean;
  isDesktop?: boolean;
  activeHover?: boolean;
  handlePopoverClose?: () => void;
  handlePopoverOpen?: () => void;
}

export interface MenuItemProps extends MenuItemShape {
  children?: JSX.Element;
  handleAction?: any;
  classes?: Record<
    'menuItem' | 'activeMenuItem' | 'menuLink' | 'menuItemIcon' | string,
    string
  >;
}

export type ImageRatio =
  | 'auto'
  | 'fixed'
  | '169'
  | '916'
  | '32'
  | '23'
  | '43'
  | '34'
  | '165'
  | '11';

export type ItemReactionShape = {
  resource_name: string;
  module_name: string;
  title: string;
  icon: string;
  color: string;
  ordering: number;
  is_default: boolean;
  id: number;
};

export type ItemReactionInformationShape = {
  id: number;
  total_reaction?: number;
};

export type ItemCommentShape = {
  user: ItemUserShape;
  text: string;
  parent_id: string;
  extra: ItemExtraShape;
  creation_date: string;
  children?: string[];
  statistic: ItemStatisticShape;
  most_reactions_information?: Array<ItemReactionInformationShape>;
  user_reacted?: string;
  extra_data?: Record<string, any>;
};

export type ActionCommentShape = {
  statistic: ItemStatisticShape;
  most_reactions_information?: Array<ItemReactionInformationShape>;
};

export type TImagePlayerOverlay = {
  icon?: string;
  size?: 'xs' | 'sm' | 'md' | 'lg';
};
export enum ImageMatureState {
  Normal = 0,
  Warning = 1,
  Strict = 2
}

export type ImageShape = {
  identity?: string;
  classes?: Record<string, any>;
  alt?: string;
  src: string;
  aspectRatio?: ImageRatio;
  shape?: 'square' | 'circle' | 'radius';
  link?: LinkProps['to'];
  host?: string;
  asModal?: boolean;
  backgroundImage?: boolean;
  playerOverlay?: boolean;
  imageFit?: 'cover' | 'contain';
  playerOverlayProps?: TImagePlayerOverlay;
  className?: string;
  imgClass?: string;
  draggable?: boolean;
  'data-testid'?: string;
  style?: Record<string, string>;
  mature?: ImageMatureState;
  linkParams?: Record<string, string>;
  matureProps?: Record<string, any>;
  onLoad?: () => void;
};

export interface TruncateTextProps extends TypographyProps {
  component?: React.ElementType;
  lines?: 0 | 1 | 2 | 3 | 4 | 5;
  fontWeight?: number;
  fixHeight?: boolean;
  showFull?: boolean;
  isIE?: boolean;
  notSupportLineClamp?: boolean;
  isSafari?: boolean;
}

export type ControlMenuItemProps = {
  item: MenuItemShape;
  handleAction?: HandleAction;
  variant?: TypographyVariant;
  as: React.ElementType;
  classes?: Record<string, string>;
  onClick?: () => void;
  className?: string;
  closeMenu?: () => void;
  active?: boolean;
};

export type TotalCommentProps = {
  total: number;
  handleAction?: HandleAction;
  message: string;
  identity: string;
  dataSource?: RemoteDataSource;
};
