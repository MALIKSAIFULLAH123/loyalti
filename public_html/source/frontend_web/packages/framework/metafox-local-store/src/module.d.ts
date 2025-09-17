import '@metafox/framework/Manager';
import { LocalStore, LocalStoreConfig } from './LocalStore';

declare module '@metafox/framework/Manager' {
  interface Manager {
    localStore?: LocalStore;
  }

  interface ManagerConfig {
    localStore?: LocalStoreConfig;
  }
}
