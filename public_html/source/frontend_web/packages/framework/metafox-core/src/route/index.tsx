import {
  MemoryRouter,
  Route,
  useLocation,
  useSearchParams
} from 'react-router-dom';

export type { RouteProps } from 'react-router-dom';
export { configureRoutes } from './configureRoutes';
export { default as HistoryServiceAware } from './HistoryServiceAware';
export { default as MainRoutes } from './MainRoutes';
export { default as ModalRoutes } from './ModalRoutes';
export { default as NavigationConfirm } from './NavigationConfirm';
export { useLocation, Route, MemoryRouter, useSearchParams };
