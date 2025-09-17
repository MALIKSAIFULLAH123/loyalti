import '@metafox/framework/Manager';
import Normalization from './Normalization';

declare module '@metafox/framework/Manager' {
  interface Manager {
    normalization?: Normalization;
  }

  interface ManagerConfig {
    chat?: Partial<TChatPlusConfig>;
  }
}
