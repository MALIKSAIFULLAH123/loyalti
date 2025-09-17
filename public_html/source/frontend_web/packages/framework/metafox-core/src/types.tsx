import { DialogItemContext } from '@metafox/dialog';
import { FormElementShape, FormSchemaShape } from '@metafox/form';
import { AppResource } from '@metafox/framework/Manager';
import { FetchDataConfig } from '@metafox/rest-client';
import { ThemeId } from '@metafox/layout';
import { MenuItemShape } from '@metafox/ui';
import { UserItemShape } from '@metafox/user';
import {
  ChipProps,
  DialogProps,
  GridProps,
  GridSize,
  GridSpacing,
  InputBaseProps,
  TableCellProps,
  TypographyVariant
} from '@mui/material';
import { SxProps } from '@mui/system';
import { DataGridProps } from '@mui/x-data-grid';
import { AnyAction, Reducer } from '@reduxjs/toolkit';
import { AxiosRequestConfig } from 'axios';
import { FormikHelpers } from 'formik';
import { ElementType } from 'react';
import {
  SagaInjectionModes,
  useInjectReducer as useReducer,
  useInjectSaga as useSaga
} from 'redux-injectors';
import { Saga } from 'redux-saga';

type Editor = Record<string, any>;
export interface AuthUserShape extends Object {
  id?: string | number;
  first_name?: string;
  user_name?: string;
  language_id?: string;
  image?: string;
  cover?: string;
  avatar?: string;
  module_name?: string;
  resource_name?: string;
  full_name?: string;
  email?: string;
  last_login?: string;
}

export interface RenderBaseItem {
  component: string | React.ElementType;
  props?: Record<string, any>;

  [otherProps: string]: any;
}

export interface HistoryState {
  as?: string;
  asModal?: boolean;
  keepScroll?: boolean;
}

export type MediaRatioVariant =
  | 'auto'
  | 'fixed'
  | '169'
  | '32'
  | '23'
  | '43'
  | '34'
  | '11';

export type MediaWidthVariant =
  | '32'
  | '48'
  | '56'
  | '80'
  | '120'
  | '160'
  | '200'
  | 'fullWidth';

export type TextColorVariant = string;

export type MediaPlacementVariant = 'start' | 'end' | 'top' | 'bottom' | 'none';

export type GridItemHoverVariant = 'none' | 'background';

export type ItemSelectedVariant = 'none' | 'background';

export type GridItemDividerVariant = 'top' | 'none' | 'bottom' | 'divider';

export type GridItemVariant =
  | 'flatted'
  | 'contained'
  | 'flattedCard'
  | 'containedCard';

export type ItemViewSpacingVariant = 'none' | 'normal' | 'dense' | 'large';

export type MaxLinesVariant = 1 | 2 | 3;

export interface ItemConfigShape {
  variant?: GridItemVariant;
  showActionMenu?: boolean;
  linkProps?: {
    asModal?: boolean;
    keepScroll?: boolean;
  };
  media?: {
    fillHeight?: boolean;
    width?: MediaWidthVariant;
    placement?: MediaPlacementVariant;
    aspectRatio?: MediaRatioVariant;
    sx?: SxProps;
    imgSx?: SxProps;
  };
  text?: {
    sx?: SxProps;
  };
  content?: {
    sx?: SxProps;
    dividerVariant?: GridItemDividerVariant;
    hoverVariant?: GridItemHoverVariant;
  };
  title?: {
    sx: SxProps;
    lines?: MaxLinesVariant;
    component?: React.ElementType;
    variant: TypographyVariant;
    color: TextColorVariant;
  };
  summary?: {
    sx?: SxProps;
    lines?: MaxLinesVariant;
    component?: React.ElementType;
    variant?: TypographyVariant;
    color?: TextColorVariant;
  };
  subInfo?: {
    sx?: SxProps;
    lines?: MaxLinesVariant;
    component?: React.ElementType;
    variant?: TypographyVariant;
    color?: TextColorVariant;
  };
}

export interface UserPreferenceConfig {
  layoutPreviewWindow?: boolean;
  userLanguage?: string;
  userDirection?: 'ltr' | 'rtl' | '';
  layoutEditMode?: number;
  previewDevice?: string;
}

export type RefOf<T> = React.MutableRefObject<T> | ((instance: T) => void);

export type AppResourceConfig = Record<string, AppResource>;

export interface LoadingComponentProps {
  size?: number;
  error?: string | boolean;
  loading?: boolean;
  sx?: SxProps;
}

export interface RenderItemBase {
  component: string | ElementType;
  props?: Record<string, any>;
}

export type RenderItemProps = RenderItemBase | RenderItemBase[];

export interface OptionItemShape {
  label: string;
  value: string | number;
}

export type PageType =
  | 'viewItem'
  | 'viewItemInModal'
  | 'browseItem'
  | 'editItem'
  | 'profile';

export interface PageParams {
  readonly appName: string;
  readonly pageName: string;
  readonly pageMetaName?: string;
  readonly breadcrumb?: boolean;
  readonly tab: string;
  readonly loginRequired?: boolean;
  readonly ready?: boolean;
  readonly heading?: JSX.Element;
  readonly _pageType: PageType;
  readonly resourceName?: string;
  readonly page?: string;
  readonly module_name?: string;
  readonly id?: string | number;
  readonly profile_id?: string | number;
  readonly profile_type?: string;
  readonly identity?: string;
}

export interface PageCreatorConfig<T> {
  readonly pageName: string;
  readonly appName: string;
  readonly defaultTab?: string;
  readonly pageType?: string;
  readonly resourceName?: string;
  readonly categoryName?: string;
  readonly loginRequired?: boolean;
  paramCreator?: (prev: T) => unknown;
}

export type HandleAction = (
  type: string,
  payload?: unknown,
  meta?: unknown
) => void;

export type ActionCreators<U = any> = (handleAction: HandleAction) => U;

export type RequiredRootState = Required<RootState>;

export type RootStateKeyType = keyof RootState;

export interface RootState {}

export type InjectedReducersType = {
  [P in RootStateKeyType]?: Reducer<RequiredRootState[P], AnyAction>;
};

export interface InjectReducerParams<Key extends RootStateKeyType> {
  key: Key;
  reducer: Reducer<RequiredRootState[Key], AnyAction>;
}

export interface InjectSagaParams {
  key: RootStateKeyType | string;
  saga: Saga;
  mode?: SagaInjectionModes;
}

/* Wrap redux-injectors with stricter types */

export function useInjectReducer<Key extends RootStateKeyType>(
  params: InjectReducerParams<Key>
) {
  return useReducer(params);
}

export function useInjectSaga(params: InjectSagaParams) {
  return useSaga(params);
}

export type UserPresenceProps = {
  language: string;
  token: string;
  name: string;
  id: string;
};

export type LayoutPreviewMode = string;

export interface JsxShape<T = Record<string, any>> {
  component: string;
  slotName?: string;
  props?: T;
  [key: string]: any;
}

export interface LayoutBlockConfigShape {
  uniqId?: string;
  component: string;
  slotName: string;
  props: {
    title?: string;
    key?: string;
  };
  [key: string]: any;
}

export interface LayoutSlotShape {
  component: string;
  props: {
    name: string;
    key?: string;
    xs?: GridSize;
    sm?: GridSize;
    md?: GridSize;
    lg?: GridSize;
    xl?: GridSize;
    [other: string]: any;
  };
}

export interface HelmetMetaShape {
  name?: string;
  property?: string;
  content: string;
  [key: string]: string;
}

export interface PageMetaShape {
  url?: string;
  title?: string;
  heading?: string;
  description?: string;
  keywords?: string;
  name?: string;
  meta?: HelmetMetaShape[];
  menu?: string;
  secondary_menu?: string;
  breadcrumbs?: { label: string; to: string }[];
}

export interface LinkShape {
  image?: string;
  title?: string;
  description?: string;
  duration?: string;
  embed_code?: string;
  host?: string;
  link?: string;
  is_preview_hidden?: number | boolean;
}

export interface AlertParams {
  title?: string;
  message: string;
  positiveButton?: { label: string };
}

export interface TModalDialogProps {
  open: boolean;
  onClose(): void;
  onExited(): void;
  onEnter?(): void;
  onEntered?(): void;
  onEntering?(): void;
  onEscapeKeyDown?(): void;
  onExit?(): void;
  onExiting?(): void;
}

export interface ConfirmParams {
  title?: string;
  message: string;
  positiveButton?: { label: string };
  negativeButton?: { label: string };
  phraseParams?: Record<string, any>;
}

export interface RemoteDataSource {
  apiUrl: string;
  apiMethod?: 'get' | 'post' | 'put' | 'patch' | 'delete';
  apiParams?: string | Record<string, any> | undefined;
  pagingId?: string;
  apiRules?: Record<string, any>;
  asFormDialog?: boolean;
  reload?: boolean;
  link?: string;
  download?: boolean; // as download
}

export interface TGridContainerProps {
  spacing?: GridSpacing;
}

export interface TGridItemProps {
  xs: GridSize;
  sm: GridSize;
  md: GridSize;
  lg: GridSize;
  xl: GridSize;
  variant: 'grid' | 'list';
}

export type ComponentSpacingKey =
  | 'pl'
  | 'pr'
  | 'pt'
  | 'pb'
  | 'mb'
  | 'ml'
  | 'mr'
  | 'mt'
  | 'mb';

export type ComponentSpacingOption = 0 | 1 | 2 | 3 | 4 | 5;

export type ComponentSpacingProps = Partial<
  Record<ComponentSpacingKey, ComponentSpacingOption>
>;

export type ComponentBorderStyle = string;

export type ComponentBorderColor = string;

export type ComponentBackgroundColor = string;

export type ComponentBorderProps = {
  borderStyle?: ComponentBorderStyle;
  borderColor?: ComponentBorderColor;
  borderRadius?: string;
};

export type ComponentProviderProps = {
  divider?: 'inset' | 'middle' | 'fullWidth';
};

export type ComponentBackgroundProps = {
  bgColor?: ComponentBackgroundColor;
  borderRadius?: string;
  maxWidth?: 'sm' | 'md' | 'lg' | '0';
  dividerVariant?: string;
  dividerColor?: string;
  fullHeight?: boolean;
};

export interface CtaActionShape {
  as?: string;
  to?: string;
  label?: string;
  color?: string;
  showWhen?: any[];
}

export interface BlockStyleProps {}

export interface BlockPropsContextShape extends BlockStyleProps {}

export interface NoContentProps {
  icon?: string;
  image?: string;
  title?: string;
  description?: string;
  variant?: string;
  noBlock?: boolean;
  contentStyle?: {
    sx?: SxProps;
  };
}

export type EmptyPageVariant =
  | 'no_content'
  | 'core.block.no_content'
  | 'hide'
  | 'core.block.no_content_with_icon'
  | 'core.block.no_results'
  | 'core.block.no_content_with_description';

export interface WithNoContentProps {
  noMedia?: boolean;
  noTitle?: boolean;
  noSummary?: boolean;
  contentStyle?: {
    sx?: SxProps;
  };
  mediaStyle?: {
    sx?: SxProps;
  };
  textStyle?: {
    sx?: SxProps;
  };
  titleStyle?: {
    component?: string;
    variant?: TypographyVariant;
    color?: TextColorVariant;
    sx?: SxProps;
  };
  summaryStyle?: {
    component?: string;
    variant?: TypographyVariant;
    color?: TextColorVariant;
    sx?: SxProps;
  };
}

export interface ConfiguredLayout {
  itemLayout?: string;
  gridLayout?: string;
  blockLayout?: string;
}

type DividerVariantProps = 'none' | 'fullWidth' | 'middle';

export interface BlockViewProps extends BlockStyleProps, ConfiguredLayout {
  title?: string;
  blockId?: string;
  titleBackend?: boolean;
  dataSource?: Partial<RemoteDataSource>;
  blockProps?: {
    blockStyle?: {
      variant?: string;
      sx: SxProps;
      dividerVariant?: DividerVariantProps;
    };
    headerStyle?: { sx: SxProps; dividerVariant: DividerVariantProps };
    titleStyle?: { sx: SxProps; component?: React.ElementType };
    contentStyle?: { sx: SxProps; dividerVariant?: DividerVariantProps };
    footerStyle?: { sx: SxProps; dividerVariant?: DividerVariantProps };
  };
  contentType?: string;
  themeId?: ThemeId;
  testid?: string;
  variant?: string; // apply preset.
  onQueryChange?: (e: any) => void;
  noHeader?: boolean;
  noFooter?: boolean;
  compose?: <T extends BlockViewProps = BlockViewProps>(
    fn: (props: T) => void
  ) => void;
  emptyPageLayout?: string;
  noContentLayout?: string;
  emptyPageProps?: {
    icon?: string;
    title?: string;
    description?: string;
    noBlock?: boolean;
    image?: string;
    contentStyle?: { sx: SxProps };
    variant?: 'default' | 'side' | 'popover' | 'center';
  };
  loadMoreTypeProp?: {
    contentStyle?: { sx: SxProps };
  };
  privacyEmptyPageProps?: {
    icon?: string;
    title?: string;
    description?: string;
    noBlock?: boolean;
  };
  errorPage?: 'hide' | 'default';
  errorPageProps?: {
    hideContent?: boolean;
  };
  gridVariant?: 'pinView' | 'listView' | 'casualView' | 'gridView';
  gridContainerProps?: {
    spacing: GridSpacing;
    [key: string]: any;
  };
  gridItemProps?: GridProps;
  itemProps?: ItemConfigShape;
  headerActions?: CtaActionShape[];
  headerActionsResource?: string;
  canLoadMore?: boolean;
  canLoadSmooth?: boolean;
  loadMoreType?: 'button' | 'scroll' | 'pagination';
  buttonMessageLoadmore?: string;
  lastReadMode?: boolean;
  displayLimit?: number;
  displayRowsLimit?: number;
  itemView?: string;
  startItemView?: any;
  authRequired?: boolean;
  paging?: PagingState;
  currentPage?: number;
  numberOfItemsPerPage?: number;
  maxPageNumber?: number;
  clearDataOnUnMount?: boolean;
  itemLinkProps?: {
    asModal?: boolean;
  };
  hasSearchBox?: boolean;
  placeholderSearchBox?: string;
  acceptQuerySearch?: boolean;
  loadMore?: () => void;
  handleUpdateLastRead?: () => void;
  messagePagination?: string;
  pagingId?: string;
  prefixPagingId?: string;
  data?: string[];
  emptyPage?: EmptyPageVariant;
  query?: string;
  showWhen?: Array<any>;
  privacyEmptyPage?: string;
  isLoadMoreScroll?: boolean;
  isLoadMoreButton?: boolean;
  isLoadMorePagination?: boolean;
  limitItemsLoadSmooth?: number;
  slotName: string;
  moduleName: string;
  resourceName?: string;
  actionName?: string;
  preventRefreshWhenEmpty?: boolean;
  actionSortName?: string;
  handleActionItem?: (value: any) => void;
  cachedEmpty?: boolean;
  isTrackingSponsor?: boolean | number;
}

type PropsCollapsible = {
  limit: number;
  as: string;
};
export interface PagingState {
  ids: string[];
  page: number;
  initialized: boolean;
  pages: Record<string, any>;
  refreshing: boolean;
  loading: boolean;
  ended: boolean;
  loadedToPage: number;
  error?: string;
  domains?: string;
  dirty?: boolean;
  offset?: Record<string, any>; // keep last response from offset to handle next request. it's helpful when server paging by offset not page number.
  pagesOffset?: Record<string, any>;
  noResultProps?: Record<string, any>;
  paginationType?: 'pagination' | 'loadmore';
  collapsible?: PropsCollapsible;
}

export interface PaginationProps {
  hideTopPagination?: boolean;
  hideBottomPagination?: boolean;
  hasSort?: boolean;
}

export interface ListViewBlockProps
  extends BlockViewProps,
    WithNoContentProps {}

export interface ListViewPaginationProps
  extends BlockViewProps,
    PaginationProps,
    WithNoContentProps {}

export interface CategoryBlockProps extends BlockViewProps {
  href?: string;
}

export type MenuItemProps = MenuItemShape;

export interface SideMenuBlockProps extends BlockViewProps {
  icon?: string;
  title?: string;
  menuName?: string;
  footerItemProps?: any;
  footerItemView: string;
  searchLabel?: string;
  searchBox?: boolean;
}

export type TExtendBlock = string | React.ElementType;

export type TStaticLayoutEditorFeature =
  | string
  | Record<string, string | string[]>;

export type ExcludeEditorConfig =
  | 'blockProps'
  | 'gridContainerProps'
  | 'itemProps';
export interface BlockEditorConfig<T extends BlockViewProps = BlockViewProps> {
  options?: Record<string, any>;
  extendBlock?: TExtendBlock;
  features?: string[];
  overrides?: Partial<Omit<T, ExcludeEditorConfig>>;
  defaults?: Partial<Omit<T, ExcludeEditorConfig>>;
  custom?: Record<string, FormElementShape>;
  customValidation?: Record<string, any>;
}

export interface SuggestionListHandle {
  moveNext(): void;
  movePrev(): void;
  selected(): () => any;
}

export interface CreateBlockParams<T extends BlockViewProps = BlockViewProps>
  extends BlockEditorConfig<T> {
  name?: string; // Component Name
  extendBlock?: TExtendBlock; // override block name
  custom?: Record<string, FormElementShape>;
}

export interface SearchBoxBlockProps extends BlockViewProps {
  placeholder?: string;
  elevation?: number;
  voice?: boolean;
  gridProps?: string;
}

export type TFetchDataResult<T> = {
  data: T;
  loading: boolean;
  error: boolean | string;
};

export type TFetchDataConfig<T = any> = {
  data?: T;
  dataSource: RemoteDataSource;
  pageParams?: Record<string, any>;
};

export type TFetchDataFn = <T = any>(
  config: TFetchDataConfig<T>
) => TFetchDataResult<T>;

export type LocalAction<P = unknown, M = unknown> = {
  type: string;
  payload: P;
  meta?: M;
};

export type GridRowId = string | number;

export type GridRowData = Record<string, any>;

export type GridDataState = {
  idField: string;
  getRowId: (row: GridRowData) => GridRowId;
  loadRev?: number;
  rows: GridRowData[];
  allRows?: GridRowData[];
  selection: GridRowId[];
  selectionCount?: number;
  indeterminate?: boolean;
  config?: SmartDataGridConfig;
  checked?: boolean;
  paging?: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
  };
};

type GridActionItem = {
  type: string;
  payload: {
    dataSource?: RemoteDataSource;
    field?: string;
  } & Record<string, any>;
};

export type GridActions = Record<string, GridActionItem>;

export interface SmartGridApi {
  config: AppResource;
  removeRow(id: GridRowId): void;
  patchRow(id: GridRowId, data: GridRowData): void;
  setRow(id: GridRowId, data: unknown): void;
  removeRows: (id: GridRowId[]) => void;
  patchRows(id: GridRowId[], data: GridRowData): void;
  patchMultiRows(data: GridRowData[]): void;
  setRows(data: GridRowData[]): void;
  toggleSelect(id: GridRowId): void;
  removeSelect(id: GridRowId): void;
  toggleSelectAll(): void;
  refresh(): void;
  orderRows(order_ids: number[]): void;
  refreshSelection(): void;
  initRows(data: GridRowData[]): void;
}

export type ItemLocalAction<
  P = { identity: string; location?: any },
  M = { setLocalState: (cb: any) => any }
> = LocalAction<P, M>;

export type FetchActionShape = LocalAction<
  FetchDetailConfig,
  {
    onFailure?: (err: any) => void;
    onSuccess?: () => void;
    abortId?: string;
  }
>;

export type GridRowAction<
  P = {
    to?: string;
    id: GridRowId;
    row: GridRowData;
    dataSource?: RemoteDataSource;
    action?: string;
    data?: unknown;
    dialogProps?: Partial<DialogProps>;
    field?: string;
    form?: string;
    confirm?: boolean | ConfirmParams;
    reload?: boolean;
    target?: string;
  },
  M = {
    apiRef: React.RefObject<SmartGridApi>;
  }
> = LocalAction<P, M>;

export type GridMassAction<
  P = {
    id: GridRowId[];
    dataSource?: RemoteDataSource;
    dialogProps?: Partial<DialogProps>;
    action?: string;
    field?: string;
    form?: string;
    reload?: boolean;
  },
  M = {
    apiRef: React.RefObject<SmartGridApi>;
  }
> = LocalAction<P, M>;

export type GridOrderAction<
  P = {
    order_ids: number[];
  },
  M = {
    apiRef: React.RefObject<SmartGridApi>;
  }
> = LocalAction<P, M>;

export type TControlAction<T, P, M = any> = {
  type: T;
  payload: P;
  meta: { state: M; setState: (prev: M) => M };
};

export type TActionControl<T = any, P = any> = (
  payload: P,
  meta: { state: T; setState: (prev: T) => T }
) => void;

export type StatusComposerState = {
  rev?: number;
  parentUser?: { item_type: string; item_id: number; name: string };
  disabled?: boolean;
  privacy?: number;
  editorStyle?: React.CSSProperties;
  textAlignment?: 'left' | 'center' | 'right';
  tags?: Record<string, { as: string; value: any; priority: number }>;
  attachments?: Record<string, { as: string; value: any }>;
  attachmentType?: string;
  className?: string;
  editing?: boolean;
};

export type BackgroundStatusProps = {
  className: string;
  textAlignment: 'left' | 'center' | 'right';
  item: Record<string, any>;
  editorStyle: React.CSSProperties;
};

export type StatusComposerRef = {
  state: StatusComposerState;
  setState: (value: StatusComposerState) => void;
  setTextAlignment(value: 'left' | 'right' | 'center'): void;
  setDisabled(value: boolean): void;
  setEditorStyle(style: React.CSSProperties): void;
  setTags(name: string, value: unknown): void;
  setAttachments(
    type: string,
    name: string,
    item: Record<string, any>,
    exclude?: string[]
  ): void;
  setForceAttachments(
    type: string,
    name: string,
    item: Record<string, any>
  ): void;
  removeAttachments(): void;
  removeAttachmentName(name: string): void;
  removeBackground(remove?: boolean): void;
  removeAttachmentLink(): void;
  hideBackground(): void;
  displayBackground(): void;
  removeTags(name: string): void;
  requestComposerUpdate?: () => void;
  setPostAsPage?: (value: boolean) => void;
  setPrivacy(value: unknown): void;
  setBackground: ({
    className,
    textAlignment,
    item,
    editorStyle
  }: BackgroundStatusProps) => void;
  setScheduleTime?: (value: string) => void;
};

export type StatusComposerControlProps = {
  isEdit?: boolean;
  isEditSchedule?: boolean;
  disabled?: boolean;
  value: any;
  editorRef: React.MutableRefObject<Editor>;
  composerRef: React.MutableRefObject<StatusComposerRef>;
  control: React.FC<{
    disabled: boolean;
    title: string;
    label: string;
    icon: string;
    testid: string;
    onClick: () => void;
    canUploadTypes?: Record<string, any>;
  }>;
  onClick?: () => void;
  onChange?: (value: unknown) => void;
  onClear?: () => void;
  focusToEndText?: () => void;
  parentIdentity?: string;
  parentType?: string;
  // post as page
  userId?: number;
  userIdentity?: string;
};

export type AttachPollPreviewProps = {
  value: any;
  formUrl: string;
  handleEdit: (value: unknown) => void;
  handleRemove: () => void;
  onClick?: () => void;
  onChange?: (value: unknown) => void;
  onClear?: () => void;
};

export type FileItemStatus = 'remove' | 'new' | 'update';

export type BasicFileItem = {
  id: number;
  uid: string;
  file?: File;
  file_name: string;
  file_type: string;
  file_size: number;
  source: string;
  caption?: string;
  image?: string;
  order?: number;
  type?: string;
  fileItemType?: string;
  status?: FileItemStatus; // using this state in order to server control
  upload_url: string;
  download_url?: string;
  extension?: string;
  text?: string;
  name?: string;
  description?: string;
  thumbnail_sizes?: Array<any>;
  storage_id?: any;
  extra_info?: Record<string, any>;
  onUploadProgress?: (event: any) => void;
};

export interface AttachmentItemShape extends BasicFileItem {
  is_video?: boolean;
  is_image?: boolean;
  file_size_text?: string;
}

export type BasicPlaceItem = {
  address?: string;
  name?: string;
  lat: number;
  lng: number;
  types?: Array<string>;
  full_address?: string;
};

export interface SessionState {
  user: UserItemShape;
  loggedIn: boolean;
  accounts: AuthUserShape[];
}

export type EncType =
  | 'application/x-www-form-urlencoded'
  | 'multipart/form-data'
  | 'text/plain';

export type FormSubmitConfig<T = Record<string, any>> = {
  initialValues?: T;
  values: T;
  action?: string;
  dialog?: boolean;
  closeDialog?: () => void;
  method?: AxiosRequestConfig['method'];
  form: FormikHelpers<T>;
  submitAction?: string;
  enctype: EncType;
  submitUrl?: string;
  dialogItem?: DialogItemContext;
  pageParams?: FetchDataConfig['pageParams'];
  secondAction?: string;
  successAction?: string;
  failureAction?: string;
  preventReset?: boolean;
  formSchema?: FormSchemaShape;
  keepPaginationData?: boolean;
  disableFormOnSuccess?: boolean;
};

export type FormSubmitAction = LocalAction<
  FormSubmitConfig,
  { onSuccess?: () => void; onFailure?: () => void; onSubmitting?: () => void }
>;

export type AclShape = Record<string, Record<string, any>>;

export type SiteSettingShape = Record<string, any>;

export interface MenuShape {
  items: MenuItemShape[];
  variant?: 'Icon' | 'IconLabel' | 'Label';
  align?: 'left' | 'right' | 'center';
}
type BreadscrumbItem = {
  to?: string;
  title?: string;
};
export interface AppUIConfig {
  sidebarHeader?: Record<
    string,
    {
      breadcrumbsData?: BreadscrumbItem[];
      title: string;
      icon: string;
      to?: string;
    }
  >;
  sidebarCategory?: {
    dataSource?: any;
    title: string;
    href: string;
    resourceName?: string;
    actionName?: string;
    appName?: string;
  };
  sidebarSearch?: {
    placeholder: string;
  };
  menus?: Record<string, MenuShape>;
}

export interface ItemViewBaseProps {
  (props: any): JSX.Element;
  displayName?: string;
}

export interface PageControlProps {
  //   history: {length: 50, action: "PUSH", location: {…}, createHref: ƒ, push: ƒ, …}
  // location: {pathname: "/friends/lists", search: "", hash: "", state: undefined, key: "hafd9a"}
  // match: {path: "/friends/lists", url: "/friends/lists", isExact: true, params: {…}}
  // history: RouteProps['history'];
}

export type FetchDetailConfig = {
  apiUrl: string;
  apiParams?: any;
  pagingId?: string;
  exceptEntities?: any;
  pageParams?: any;
};

export interface PagingPayload {
  apiUrl: string;
  apiParams: any;
  pagingId: string;
  isLoadMorePagination?: boolean;
  isLoadMoreButton?: boolean;
  numberOfItemsPerPage?: number;
  lastReadMode?: boolean;
  lastIdMode?: boolean;
  maxPageNumber?: number;
}

export interface PagingMeta {
  successAction?: { type: string; payload: unknown };
  failedAction?: { type: string; payload: unknown };
  abortId?: string;
}

export type PageMetaDataState = Record<string, PageMetaShape>;
export type LinkState = Record<string, LinkShape>;

export interface Address {
  address?: string;
  lat: number;
  lng: number;
  icon?: string;
  name?: string;
  query?: string;
  show_map?: boolean;
  full_address?: string;
}

export type SmartDataGridProps = Omit<DataGridProps, 'rows' | 'pagination'> & {
  gridName: string;
  rows?: GridRowData[];
  title?: string;
  hideTitle?: boolean;
  hideDescription?: boolean;
  description?: string;
  size?: TableCellProps['size'];
  batchActionMenu?: MenuShape;
  itemActionMenu?: MenuShape;
  hideToggleSearch?: boolean; // default = false, has filter ?
  fetchAction?: RemoteDataSource;
  remoteSearchForm?: RemoteDataSource;
  searchForm?: FormSchemaShape;
  searchFormVisible?: boolean; // default = trues
  minHeight?: number | string;
  wrapper?: React.ElementType;
  height?: number;
  pagination?: boolean;
  config: SmartDataGridConfig;
  footerHeight?: number;
  allowRiskParams?: boolean;
  errorComponent?: string;
};

export type SmartDataGridConfig = SmartDataGridProps & {
  gridName?: string;
  title?: string;
  inlineSearch?: string[];
  hideTitle?: boolean;
  hideDescription?: boolean;
  description?: string;
  size?: TableCellProps['size'];
  batchActionMenu?: MenuShape;
  itemActionMenu?: MenuShape;
  hideToggleSearch?: boolean; // default = false, has filter ?
  fetchAction?: RemoteDataSource;
  searchFormPlacement?: 'top' | 'header'; // default="top"
  remoteSearchForm?: RemoteDataSource;
  dataSource?: RemoteDataSource;
  searchForm?: FormSchemaShape;
  searchFormVisible?: boolean; // default = trues
  minHeight?: number | string;
  height?: number;
  pagination?: boolean;
  idField?: string;
  actions?: Record<string, RemoteDataSource>;
  sortable?: boolean;
  additionalSection?: Record<string, any>;
  dynamicRowHeight?: boolean;
  rowsPerPage?: number;
  manualColumnItemActionMenu?: boolean;
};

export interface DataGridContextShape {
  gridState: GridDataState;
  classes: Record<string, any>;
  config: SmartDataGridConfig;
  apiRef: React.RefObject<SmartGridApi>;
  dispatch: (action: unknown) => void;
  handleRowAction: (type: string, payload?: object, meta?: object) => void;
  handleColumnAction: (type: string, payload?: object, meta?: object) => void;
  handleBatchAction: (type?: string, payload?: object, meta?: object) => void;
  handleReloadRow: (type?: string, payload?: object, meta?: object) => void;
  handleGridAction: (type: string, payload?: object, meta?: object) => void;
  setSearchValues?: React.Dispatch<React.SetStateAction<Record<string, any>>>;
}

export interface PickerItemControlProps {
  item: {
    title: string;
    avatar: string;
    image: Record<string, string>;
    module_name?: string;
    resource_name?: string;
    cover?: Record<string, string>;
  };
  classes: Record<string, string>;
  selected?: boolean;
  onClick?: () => void;
  wrapAs: any;
  wrapProps: any;
}

export interface SearchInputProps extends InputBaseProps {
  onChanged: (value: string) => void;
}

export interface SingleItemPickerDialogProps<T = Record<string, any>>
  extends Pick<DialogProps, 'maxWidth' | 'fullWidth'> {
  onChanged: (value: string) => void;
  items: T[];
  selectedItem: T;
  onSelectItem: (item: T) => void;
  searchInput?: React.FC<SearchInputProps>;
  loading?: boolean;
  placeholder: string;
  dialogTitle: string;
  emptyPage?: string;
  gridLayout: string;
  itemLayout: string;
  itemView: string;
  emptyPageProps?: {
    title?: string;
  };
  'data-testid': string;
}

export interface MultipleItemPickerDialogProps<T = Record<string, any>>
  extends Pick<DialogProps, 'maxWidth' | 'fullWidth'> {
  onChanged: (value: string) => void;
  items: T[];
  selectedItems: T[];
  onSelectItem: (item: T) => void;
  chipControl?: React.FC<ChipProps>;
  chipLabel?: string;
  searchInput: React.FC<SearchInputProps>;
  loading?: boolean;
  error?: string;
  placeholder: string;
  dialogTitle: string;
  leftButton?: boolean;
  emptyPage?: string;
  emptyPageProps?: {
    title?: string;
  };
  itemView: string;
  gridLayout: string;
  itemLayout: string;
  heightContent?: string;
}

export interface CustomFieldShape {
  id: string;
  icon: string;
  label: string;
  value: any;
  description: any;
  status: any;
}

export interface CustomSectionShape {
  id: string;
  label: string;
  component: string;
  description?: string;
  fields?: Record<string, CustomFieldShape>;
}
