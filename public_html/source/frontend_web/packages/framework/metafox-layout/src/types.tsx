import { Manager, WithNoContentProps } from '@metafox/framework';
import { JsxBackendConfig } from '@metafox/jsx';
import { SxProps, Theme, ThemeOptions } from '@mui/material';
import { ContainerProps } from '@mui/material/Container';
import { GridProps, GridSize, GridSpacing } from '@mui/material/Grid/Grid';
import { Breakpoint } from '@mui/system';

export type ItemLayoutShape = Record<string, any>;
export type GridLayoutShape = Record<string, any>;
export type ThemeType = 'light' | 'dark' | 'auto';

export type LayoutSlotPointKey =
  | 'xs1'
  | 'xs2'
  | 'xs3'
  | 'xs'
  | 'sm1'
  | 'sm'
  | 'md'
  | 'lg'
  | 'xl';

export type ThemeId = string;

export type LayoutBackgroundColorKey = 'paper' | 'auto';

export interface ThemeLayoutSlotOptions {
  stickyOffsetTop?: number;
  points: Record<LayoutSlotPointKey, number>;
  background: Record<LayoutBackgroundColorKey, any>;
}

export interface ThemeVariant {
  default?: Partial<ThemeOptions>;
  dark?: Partial<ThemeOptions>;
}

export interface ThemeInfo {
  id: string;
  dir: string;
  name: string;
  system?: boolean;
}

export interface LoadedTheme {
  info: ThemeInfo;
  views: JsxBackendConfig;
  default: {
    siteBlocks: Record<PageSize, JsxShape[]>;
    gridLayouts: Record<string, GridLayoutShape>;
    blockLayouts: Record<string, LayoutShape>;
    itemLayouts: Record<string, LayoutShape>;
    noContentLayouts: Record<string, WithNoContentProps>;
    originSiteDocks: any[];
    originSiteFixedDocks: any[];
    templates: TemplateData;
    pageLayouts: LayoutShape;
    originSiteBlocks: Record<PageSize, JsxShape[]>;
    originGridLayouts: Record<string, GridLayoutShape>;
    originBlockLayouts: Record<string, LayoutShape>;
    originItemLayouts: Record<string, LayoutShape>;
    originNoContentLayouts: Record<string, WithNoContentProps>;
    originTemplates: TemplateData;
    originPageLayouts: LayoutShape;
    processors: ((theme: Theme) => void)[];
    styles: ThemeVariant;
  };
}

export interface VariantInfo {
  name: string;
  id: string;
  dir: string;
  system?: boolean;
}

export interface LoadedVariant {
  info: VariantInfo;
  default: ThemeVariant;
}

export interface BlockFeatureCreatorConfig {
  manager: Manager;
  theme: Theme;
  features: string[];
  disabled: Record<string, boolean>;
  config: Record<string, any>;
  extra: Record<string, any>;
}

export type BlockFeatureCreator = (option: BlockFeatureCreatorConfig) => any;

export type CreatePageParams<T extends Object> = (
  props: any,
  defaults?: Partial<T>,
  cb?: (prev: T) => T
) => T;

export type CreateContentParams<T = Record<string, any>> = (data: T) => T;

export type UsePageParams = () => Record<string, string>;

export type UseMasterPageConfig = (
  process: (pathname: string) => string
) => MasterPageConfig;

export type LayoutUpdate = (data: LayoutPageStateShape) => void;

export type UsePageBlocks = () => any;

// first: [0]? default is true
// second: [1]? default is false
export type LayoutScope = boolean;

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

export interface PageMetaShape {
  title?: string;
  description?: string;
  metas?: HelmetMetaShape[];
}

export interface LayoutPageLocationShape {
  [key: string]: any;
}

export enum EditMode {
  launch = 0,
  editPageContent = 1,
  editLive = 2,
  editLayout = 4,
  editSiteContent = 8
}

export type LayoutPreviewMode = string;

export type ContentParams = Record<string, any> | undefined;

/**
 * <=767 : small
 * <=991 : medium
 * <=1199: large
 */
export type PageSize = 'small' | 'sMedium' | 'medium' | 'large';

export interface TemplateSlotConfig {
  component: string;
  slotName: string;
  name: string;
  key?: string;
  xs?: GridSize;
  sm?: GridSize;
  md?: GridSize;
  lg?: GridSize;
  xl?: GridSize;
  [other: string]: any;
  elements?: Record<string, TemplateSlotConfig>;
}

export interface TemplateContainerParams {
  spacing?: GridSpacing;
  maxWidth?: string | false;
  gridContainer?: boolean;
  disableGutters?: boolean;
}

export interface ContainerConfig extends TemplateContainerParams {
  component?: string;
  elements?: Record<string, TemplateSlotConfig>;
}

export interface SectionConfig {
  elements?: ContainerConfig;
}

export interface TemplateConfig {
  title?: string;
  description?: string;
  thumbnail?: string;
  templateName?: string;
  overrides: Record<string, object>;
  blocks: JsxShape[];
  header: SectionConfig;
  content: SectionConfig;
  footer: SectionConfig;
  info: {
    bundle?: string;
    disabled?: boolean;
  };
}

export interface JsxShape<T = Record<string, any>> {
  component: string;
  slotName?: string;
  props?: T;
  [key: string]: any;
}

export interface PageLayoutShape {
  templateName?: string;
  blocks?: JsxShape[];
  // block overrides configuration, help to support disabled, ordering to merge to blocks
  // helpful when ordering priority layout and order.
  overrides?: Record<string, object>;
  header?: SectionConfig;
  footer?: SectionConfig;
  content?: SectionConfig;
}

export interface LayoutPageStateShape {
  pageName: string;
  pageNameAlt?: string;
  templateName: string;
  pageSize: PageSize;
  header?: any;
  content?: any;
  footer?: any;
  blocks: JsxShape[];
  layoutEditMode?: EditMode; // default = false
  previewDevice?: LayoutPreviewMode; // default = 0
  pageParams?: Record<string, any>;
  pageHelmet?: PageMetaShape;
  contentParams?: ContentParams;
  asMasterPage?: boolean;
}

export type TemplateData = Record<string, TemplateConfig>;

export interface LayoutShape {
  [key: string]: Record<PageSize, PageLayoutShape> & {
    info: { title: string; description: string };
    pageNameAlt?: string;
  };
}

export type MasterPageConfig = {
  transformPathname: (pathname: string) => string | undefined;
};

export type UpdatePageProps = {
  pageName: string;
  pageNameAlt?: string;
  pageNameAsGuest?: string;
  templateName?: string;
  pageSize: PageSize;
  previewDevice?: LayoutPreviewMode;
  loginRequired?: boolean;
  layoutEditMode?: EditMode;
  pageParams?: Record<string, any>;
  pageLayout?: PageLayoutShape;
  pageHelmet?: PageMetaShape;
  contentParams?: ContentParams;
  masterPage?: MasterPageConfig;
  update: LayoutUpdate;
  isLogged: boolean;
};

export interface BlockControlProps {
  index: number;
  title?: string;
  parentBlockId: string;
  blockId: string;
  blockName: string;
  blockOrigin: BlockOrigin;
  templateName: string;
  elementPath: string;
  pageName: string;
  layoutEditMode: EditMode;
  blockDisabled: boolean;
  pageSize: PageSize;
  slotName: string;
  elements: any;
  extra?: any;
  blockOrder?: number;
}

export type EditBlockMode =
  | 'editItemLayout'
  | 'editGridLayout'
  | 'editBlockLayout'
  | 'editBlock'
  | 'editNoContentLayout';

export interface EditLayoutBlockEditorProps extends BlockControlProps {
  blockId: string;
  blockName: string;
  pageName: string;
  pageSize: PageSize;
  slotName: string;
}

export type LayoutBlockViewPropsContext = {
  variant?: string;
  noHeader?: boolean;
  noFooter?: boolean;
  [key: string]: any;
};

export type LayoutSlotContextShape<T = Record<string, any>> = [
  T,
  (prev: T) => T
];

type SlotWidth = 'xs1' | 'xs2' | 'xs' | 'sm' | 'md' | 'lg' | 'xl';

export type LayoutSlotProps = {
  sectionName?: string;
  slotName: string;
  master?: boolean;
  showEmpty?: boolean;
  templateName: string;
  pageName?: string;
  pageSize?: PageSize;
  elementPath: string;
  themeName?: string;
  gridContainer?: boolean;
  priority?: number;
  previewDevice?: LayoutPreviewMode;
  layoutEditMode?: EditMode;
  elements?: any[];
  blocks: JsxShape[];
  width?:
    | '1'
    | '2'
    | '3'
    | '4'
    | '5'
    | '6'
    | '7'
    | '8'
    | '9'
    | '10'
    | '11'
    | '12';
  flexWeight?: '0' | '1' | '2' | '3' | '4' | '5';
  freeze?: 0 | 1 | 2 | 3;
  disabledScroll?: boolean;
  embedChildPage?: boolean; // default = false
  rootStyle: {
    maxWith: SlotWidth;
    minWidth: SlotWidth;
    sx: SxProps;
  };
  stageStyle: {
    maxWith: SlotWidth;
    minWidth: SlotWidth;
    minHeight: string;
    sx: SxProps;
  };
  contentStyle: {
    maxWith: SlotWidth;
    minWidth: SlotWidth;
    sx: SxProps;
  };
} & GridProps &
  ContainerProps;

export type LayoutSlotControllerProps = {} & LayoutSlotProps;

export interface HelmetMetaShape {
  name?: string;
  property?: string;
  content: string;
  [key: string]: string;
}

export type LayoutUpdater = (prev: JsxShape[]) => JsxShape[];

export type BlockUpdater = (prev: JsxShape) => JsxShape;

export type LayoutSubscription = (data: LayoutPageStateShape) => void;

export type BlockOrigin = 'page' | 'site' | 'layout';

export type EditBlockParams = {
  title?: string;
  blockId: string;
  blockName: string;
  blockOrigin: BlockOrigin;
  pageName: string;
  pageSize: PageSize;
  slotName?: string;
  editMode?: EditBlockMode;
};

export type EditContainerParams = {
  templateName: string;
  containerName: string;
  elementPath: string;
};

export type EditSlotParams = {
  slotName: string;
  templateName: string;
  elementPath: string;
};

export type TMasterOption = {
  value: string;
  label: string;
  description?: string;
};

export interface SelectMasterPageProps {
  pageName: string;
  pageSize: PageSize;
  templateName: string;
}

export interface BlockViewConfig {
  name: string;
  title?: string;
  keywords?: string;
  description?: string;
  previewImage?: string;
  container?: boolean;
  experiment?: boolean;
  admincp?: boolean;
  bundle?: 'admincp' | 'web' | 'install' | string;
  hiddenOnEditorMode?: boolean;
}

export type BlockViewDictionary = Record<string, BlockViewConfig>;

export interface TItemViewConfig {
  name: string;
  title?: string;
}

export type TItemViewDictionary = Record<string, TItemViewConfig>;

export interface TEmbedViewConfig {
  name: string;
  title?: string;
  keywords?: string;
  description?: string;
  previewImage?: string;
}

export type TEmbedViewDictionary = Record<string, TEmbedViewConfig>;

export interface LayoutBackendConfig {
  blockFeatures: Record<string, BlockFeatureCreator>;
  pages: LayoutShape;
  siteDock: string[];
}

export interface LayoutContainerProps extends ContainerProps {
  elementPath: string;
  containerName: 'header' | 'footer' | 'main' | string;
  themeType?: 'dark' | 'light' | 'auto';
  wrap?: 'wrap' | 'nowrap';
  editable?: boolean;
  master?: boolean;
  elements?: any[];
  sectionName?: string;
  templateName?: string;
  layoutEditMode?: EditMode;
  disableGutters?: boolean;
  rootStyle: {
    maxWidth?: Breakpoint;
    sx: SxProps;
  };
}

export type UIBlockProps = {
  children?: any;
  blockProps: any;
};
