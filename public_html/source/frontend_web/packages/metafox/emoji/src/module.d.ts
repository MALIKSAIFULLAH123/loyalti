import '@metafox/framework/Manager';
import { AppState, AttachEmojiButtonProps } from './types';

declare module '@metafox/framework/Manager' {
  interface GlobalState {
    emoji?: AppState;
  }
  interface Manager {
    AttachEmojiButton?: React.FC<AttachEmojiButtonProps>;
  }
}
