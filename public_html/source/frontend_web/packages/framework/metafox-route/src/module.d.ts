import '@metafox/framework/Manager';
import { RouteBackend } from './RouteBackend';
import { RouteBackendConfig } from './types';

declare module '@metafox/framework/Manager' {
  interface Manager {
    routeBackend?: RouteBackend;
    useRoutePrompt: any;
  }
  interface ManagerConfig {
    routeBackend?: Partial<RouteBackendConfig>;
  }
}
