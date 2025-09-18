import '@metafox/framework/Manager';
import ChatBackend from './services/ChatplusBackend';
import ChatplusDock from './services/ChatplusDock';
import { AppState } from './types';

declare module '@metafox/framework/Manager' {
  interface GlobalState {
    chatplus: AppState;
  }

  interface Manager {
    chatplus?: ChatBackend;
    chatplusDock?: ChatplusDock;
  }

  interface ManagerConfig {
    chat?: Partial<ChatPlusConfig>;
  }
}
