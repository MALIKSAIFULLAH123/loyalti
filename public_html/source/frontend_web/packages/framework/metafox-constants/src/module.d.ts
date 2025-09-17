import '@metafox/framework/Manager';
import Constants from './Constants';

declare module '@metafox/framework/Manager' {
  interface Manager {
    constants?: Constants;
  }
}
