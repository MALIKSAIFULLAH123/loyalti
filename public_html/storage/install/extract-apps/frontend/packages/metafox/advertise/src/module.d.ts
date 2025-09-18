import '@metafox/framework/Manager';
import { AppState } from './types';

declare module '@metafox/framework/Manager' {
  interface GlobalState {
    advertise?: AppState;
  }
  interface Manager {
    LinkTrackingSponsor?: React.FC<any>;
    InViewTrackingSponsor?: React.FC<any>;
  }
}
