import {
  AppState,
  ProfileHeaderAvatarProps,
  ProfileHeaderCoverProps
} from './types';

declare module '@metafox/framework/Manager' {
  interface GlobalState {
    user?: AppState;
  }

  interface Manager {
    ProfileHeaderCover?: React.FC<ProfileHeaderCoverProps>;
    ProfileHeaderAvatar?: React.FC<ProfileHeaderAvatarProps>;
    UserAvatarsGroup?: React.FC<UserAvatarsGroupProps>;
  }
}
