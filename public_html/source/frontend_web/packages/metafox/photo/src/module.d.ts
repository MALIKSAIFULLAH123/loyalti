import '@metafox/framework/Manager';
import { AppState, AttachPhotoButtonProps } from './types';

declare module '@metafox/framework/Manager' {
  interface Manager {
    AttachPhotoButton?: React.FC<AttachPhotoButtonProps>;
  }
  interface GlobalState {
    photo?: AppState;
  }
}
