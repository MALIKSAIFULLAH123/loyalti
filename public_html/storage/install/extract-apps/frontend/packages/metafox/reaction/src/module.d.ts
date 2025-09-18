import '@metafox/framework/Manager';
import { AppState } from './types';

declare module '@metafox/framework/Manager' {
  interface GlobalState {
    preaction?: AppState;
  }

  interface Manager {
    ReactionResult?: React.FC<MostReactionsProps>;
    ReactionActButton?: React.FC<{
      minimize?: boolean;
      onlyIcon?: boolean;
      reacted?: string;
      identity?: string;
      handleAction?: any;
    }>;
  }

  interface ManagerConfig {}
}
