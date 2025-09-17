import '@metafox/framework/Manager';
import { Theme, GridProps } from '@mui/material';
import { Breakpoint } from '@mui/material/styles';
import * as H from 'history';
import { Location, NavigateFunction } from 'react-router';
import { MenuShape, PageMetaShape, SiteSettingShape, AclShape } from '.';
import EventCenter from './EventCenter';
import SmartDataGridApi from './smart-data-grid/SmartDataGridApi';
import UserPreferenceBackend from './userPreferences/UserPreferenceBackend';
import {
  ActionCreators,
  GridDataState,
  HandleAction,
  LoadingComponentProps,
  PagingState,
  UserPreferenceConfig
} from './types';
import React from 'react';
import Firebase from './Firebase';

export type Selector<T = any> = (state: any) => T;
export type SelectFn = (selector: Selector<T>) => T;
export type SelectItem<T = any> = (name: string) => T;
declare module '@metafox/framework/Manager' {
  interface Manager {
    eventCenter?: EventCenter;
    BlockLoading?: React.FC<LoadingComponentProps>;
    selfLink?: (url: string) => string;
    useResourceMenu?: (
      appName: string,
      resourceName: string,
      menuName: string
    ) => MenuShape | undefined;
    compactData?: (
      obj1: Record<string, any> | string | undefined,
      obj2: Record<string, any> | string | undefined,
      rules?: Record<string, any>,
      strict?: boolean
    ) => Record<string, any>;
    compactUrl?: (
      url: string,
      params: Record<string, any> | string | undefined
    ) => string;
    redirectTo?: (url: string) => void;
    useGetItem?: (identity: string) => Record<string, any> | undefined;
    useActionControl?: <T extends unknown, U extends unknown>(
      identity: string,
      state: T,
      actionCreators?: ActionCreators<U>
    ) => [HandleAction, T, (prev: T) => void, U];
    ScrollToTopOnMount?: React.FC<{}>;
    select?: SelectFn<T>;
    navigate?: NavigateFunction;
    useTheme?: () => Theme;
    useWidthBreakpoint?: () => Breakpoint;
    useSession?: () => SessionState;
    location?: Location;
    useLimitAttachment?: () => number;
    useLimitFileSize?: () => Record<string, any>;
    ParserPreviewPhoto?: React.FC<{}>;
    useCheckFileUpload?: (
      data: Record<string, any>
    ) => [(x: FileList) => FileList];
    getSetting?: () => SiteSettingShape;
    getSetting?: <T = string>(name: string, defaultValue?: T) => T;
    getAcl: () => AclShape;
    getAcl: <T = boolean>(name: string, defaultValue?: T) => T;
    useSmartDataGrid: (
      config: SmartDataGridConfig,
      initState: GridDataState
    ) => [
      React.ReducerState<GridDataState, any>,
      React.Dispatch<React.ReducerAction<any>>,
      React.RefObject<SmartDataGridApi>
    ];
    UserPreferenceProvider?: React.FC<{}>;
    preferenceBackend?: UserPreferenceBackend;
    usePreference?: () => UserPreferenceConfig;
    useLoggedIn?: () => boolean;
    usePrevious?: (value: T) => T | undefined;
    useAdmincpSiteLoading?: () => boolean;
    BatchSelectProvider?: T;
    useBatchSelectContext?: T;
    useReactions?: () => any[];

    /**
     * Display this component when main route is waiting for resolve page
     */
    RouteWaitingView?: React.FC<{}>;

    /**
     * Display this component when main route is loading
     */
    RouteLoadingView?: React.FC<{}>;

    /**
     * set route is loading
     */
    setRouteLoading?: (loading: boolean) => void;

    /**
     * set trigger scroll to top when route change
     */
    triggerScrollTop?: () => void;

    /**
     * Handle page loading indicator
     */
    PageLoadingIndicator?: React.FC<{}>;

    /**
     * handle root router, see "name: RootRouter"
     */
    RootRouter?: React.FC<{ test?: boolean }>;

    /**
     * handle error boundary, see "name: ErrorBoundary"
     */
    ErrorBoundary?: React.FC<{ errorPage?: string; children: unknown }>;

    /**
     * Check current view is mobile layout
     */
    useIsMobile?: (acceptTablet?: boolean) => boolean;

    /**
     * get item by identity
     */
    useGetItem?: <T>(identity: string) => ?T;

    /**
     * get items by identities
     */
    useGetItems?: <T>(identities: string[]) => T[];

    /**
     * get action items by identities
     */
    useGetActionHeader?: <T>(path: string) => T[];

    /**
     * Get app menu
     */
    useAppMenu?: (app: string, menu: string) => MenuShape | undefined;

    useSubject<T>(): T | undefined;

    /**
     * Get page meta data
     */
    usePageMeta?: () => PageMetaShape;

    /**
     * get asset url
     */
    assetUrl?: (name: string) => string;
    /**
     * proxy to redux dispatch
     */
    dispatch?: (action: {
      type: string;
      payload?: unknown;
      meta?: unknown;
    }) => void;
    getState: () => GlobalState;
    useSessionSummary?: () => SessionState;

    /**
     * Get error message
     */
    getErrorMessage(err: unknown): string;

    /**
     * Quick handle action error
     * @param err
     */
    handleActionError(err: unknown): void;

    createErrorPage: (
      error: unknown,
      params?: {
        loginRequired?: boolean;
      }
    ) => React.FC<{}>;

    firebaseBackend?: Firebase;

    isMobile?: boolean;

    goSmartBack?: () => void;
  }

  interface ManagerConfig {
    settings?: Partial<UserPreferenceConfig>;
  }

  interface GlobalState {
    pagination?: Record<string, PagingState>;
    pageMeta?: PageMetaDataState;
  }
}
