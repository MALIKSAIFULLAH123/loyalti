import '@metafox/framework/Manager';
import ChatBackend from './services/ChatBackend';
import { AppState } from './types';

declare module '@metafox/framework/Manager' {
  interface GlobalState {
    chat: AppState;
  }

  interface Manager {
    chatBackend?: ChatBackend;
  }

  interface ManagerConfig {
    chat?: Partial<ChatConfig>;
  }
}
