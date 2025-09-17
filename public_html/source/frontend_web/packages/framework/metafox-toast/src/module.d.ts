import '@metafox/framework/Manager';
import ToastBackend from './ToastBackend';
import { ToastBackendConfig } from './types';

declare module '@metafox/framework/Manager' {
  interface Manager {
    toastBackend?: ToastBackend;
    ToastContainer?: React.FC<{}>;
  }

  interface ManagerConfig {
    toast?: Partial<ToastBackendConfig>;
  }
}
