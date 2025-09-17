import '@metafox/framework/Manager';
import DialogBackend from './DialogBackend';
import {
  AlertParams,
  ConfirmParams,
  DialogBackendConfig,
  DialogItemContext
} from './types';

declare module '@metafox/framework/Manager' {
  interface Manager {
    dialogBackend?: DialogBackend;
    useDialog?: () => DialogItemContext;
    DialogContainer?: React.FC<{}>;
  }

  interface ManagerConfig {
    dialog?: Partial<DialogBackendConfig>;
  }

  interface AppResourceAction {
    alert?: AlertParams;
    confirm?: ConfirmParams | boolean;
  }
}
