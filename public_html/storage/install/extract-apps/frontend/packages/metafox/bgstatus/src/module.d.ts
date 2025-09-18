import '@metafox/framework/Manager';
import { AppState, BgStatusListBaseProps } from './types';

declare module '@metafox/framework/Manager' {
  interface Manager {
    BgStatusList?: React.FC<BgStatusListBaseProps>;
  }
  interface GlobalState {
    bgstatus?: AppState;
  }
}
