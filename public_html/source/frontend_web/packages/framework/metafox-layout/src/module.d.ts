import { PageParams } from '@metafox/framework';
import { ConfirmParams } from '@metafox/dialog';
import React from 'react';
import LayoutBackend from './LayoutBackend';
import PopoverBackend, { PopoverHandlerConfig } from './Popover/PopoverBackend';
import {
  CreateContentParams,
  LayoutBackendConfig,
  LayoutPageStateShape,
  LayoutScope,
  UsePageBlocks
} from './types';
import ThemeProcessor from './ThemeProcessor';
import { Theme } from '@mui/material';

declare module '@metafox/framework/Manager' {
  interface Manager {
    LayoutPreviewContainer?: React.FC<{ url: string; previewDevice: string }>;
    LayoutProvider?: React.FC<{}>;
    LayoutScopeContainer?: React.FC<{ value: LayoutScope }>;
    LayoutSection?: React.FC<{}>;
    getPageParams?: <T extends {} = {}>() => T & PageParams;
    layoutBackend?: LayoutBackend;
    useContentParams?: () => any;
    createContentParams?: CreateContentParams;
    useLayout?: () => LayoutPageStateShape;
    useCachedBlockEmpty?: (name: string) => [boolean, (x: boolean) => void];
    createPageParams?: <T = unknown>(
      props: any,
      next1?: (prev: T) => unknown,
      next2?: (prev: T) => unknown,
      next3?: (prev: T) => unknown,
      next4?: (prev: T) => unknown,
      next5?: (prev: T) => unknown
    ) => T;
    usePageParams?: UsePageParams;
    usePageBlocks?: UsePageBlocks;
    useLayoutProvider?: () => (data: LayoutPageStateShape) => void;
    isInMasterLayout?: () => boolean;
    isInMasterPage?: () => boolean;
    useScrollRef?: () => React.MutableRefObject<any>;
    ScrollProvider?: React.FC<{ scrollRef: React.MutableRefObject<any> }>;
    useMasterPageConfig?: UseMasterPageConfig;
    DraggingContainer: React.FC<{}>;
    copyToClipboard?: (text: string) => void;
    popoverBackend?: PopoverBackend;
    setNavigationConfirm?: (
      when: boolean,
      confirm?: ConfirmParams | boolean,
      callback?: () => void
    ) => void;

    getNavigationConfirm?: () => {
      confirm: React.MutableRefObject<ConfirmParams>;
      onOk: React.MutableRefObject<() => void>;
    };
    SiteDockContainer?: React.FC<{}>;
    SiteFixedDockContainer?: React.FC<{}>;

    /**
     * Check current view is rotating
     */
    useLayoutPageSize?: () => PageSize;

    useSticky?: (x: Record<string, any>) => { top: string; position: string };

    theme?: Theme;
    themeProcessor?: ThemeProcessor;
    ThemeProvider?: React.ElementType;

    ToggleGroupProvider: React.ElementType;

    useToggleGroup?: (
      group: string,
      name: string,
      initial?: boolean
    ) => [boolean, () => void];

    useMediaPlaying?: (
      name: string,
      initial?: boolean
    ) => [boolean, (x: boolean, force?: boolean) => void];
    useInAppBar?: () => [boolean];
  }

  interface ManagerConfig {
    layouts?: LayoutBackendConfig;
    popoverHandlers?: PopoverHandlerConfig[];
  }
}
