import '@metafox/framework/Manager';
import { CookieBackend } from './CookieBackend';
import { CookieBackendConfig } from './types';

declare module '@metafox/framework/Manager' {
  interface Manager {
    cookieBackend?: CookieBackend;
  }

  interface RootConfig {
    cookie?: Partial<CookieBackendConfig>;
  }
}
