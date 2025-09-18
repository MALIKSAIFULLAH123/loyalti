import '@metafox/framework/Manager';
import { AppState, AttachStickerButtonProps } from './types';

declare module '@metafox/framework/Manager' {
  interface Manager {
    AttachStickerButton?: React.FC<AttachStickerButtonProps>;
  }
  interface GlobalState {
    sticker?: AppState;
  }
}
