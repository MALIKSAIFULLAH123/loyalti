import '@metafox/framework/Manager';
import { ShareActButtonProps } from './components/ShareActButton';
import { AppState } from './types';
declare module '@metafox/framework/Manager' {
  interface Manager {
    ShareActButton?: React.FC<ShareActButtonProps>;
  }
  interface GlobalState {
    share?: AppState;
  }
}
