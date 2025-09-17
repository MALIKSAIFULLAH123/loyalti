import '@metafox/framework/Manager';
import { AppState } from './types';

declare module '@metafox/framework/Manager' {
  interface GlobalState {
    notification?: AppState;
  }
}
