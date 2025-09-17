import '@metafox/framework/Manager';
import JsxBackend from './JsxBackend';
import { JsxBackendConfig } from './types';

declare module '@metafox/framework/Manager' {
  interface Manager {
    jsxBackend?: JsxBackend;
  }
  interface ManagerConfig {
    views?: JsxBackendConfig;
  }
}
