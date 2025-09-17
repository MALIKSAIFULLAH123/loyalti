import { AppResource, AppUIConfig, LocalAction } from '@metafox/framework';
import {
  EmbedItemInFeedItemProps,
  ItemShape,
  ItemViewProps,
  MenuItemShape
} from '@metafox/ui';

export type ProfileMenuItemProps = {
  classes?: Record<'menuItem' | 'activeMenuItem' | 'menuLink' | string, string>;
} & MenuItemShape;

export type ProfileMenuProps = {
  menuItems?: MenuItemShape[];
  activeTab?: string;
  [key: string]: any;
};

export type UserItemShape = ItemShape & {
  user_name: string;
  first_name?: string;
  last_name?: string;
  full_name?: string;
  location: string;
  country_name?: string;
  country_state_name?: string;
  summary: string;
  joined?: string;
  statistic: Record<string, any>;
  avatar?: string;
  avatar_id?: string;
  cover?: string;
  link: string;
  cover_photo_id?: string;
  friendship?: number;
  _identity?: string;
  cover_photo_position: string;
  city_location?: string;
  profile_settings?: Record<string, boolean>;
  profile_menu_settings?: Record<string, boolean>;
  bio: string;
  hobbies: string;
  interest: string;
  about_me: string;
  birthdate?: string;
  relationship?: string;
  sort_feed_preferences?: any;
};

export type MultiFactorAuthSettings = {
  description: string;
  icon: string;
  is_active: boolean;
  service: string;
  title: string;
};

export type UserAvatarPhoto = ItemShape;

export type UserAvatarPhotoProps = ItemViewProps<UserAvatarPhoto>;

export type UserItemState = { menuOpened?: boolean };

export type UserItemActions = {
  toggleMenu: () => void;
  editProfileHeaderAvatar: () => void;
  presentMutualFriends: () => void;
  presentFriends: () => void;
  addFriend: () => void;
  acceptFriend: () => void;
  denyFriend: () => void;
  chatWithFriend: (isMobile?: boolean) => void;
  cancelRequest: () => void;
  cancelInvitation: () => void;
};

export type UserItemProps = ItemViewProps<
  UserItemShape,
  UserItemActions,
  UserItemState
> & {
  itemActionMenu: Array<Record<string, any>>;
};

export type EmbedUserInFeedItemProps = EmbedItemInFeedItemProps<UserItemShape>;

export type UserAvatarsGroupProps = {
  classes?: any;
  data: string[];
  limit?: number;
};

export type ProfileHeaderAvatarProps = {
  alt: string;
  avatar: string;
  avatarId?: string;
  canEdit?: boolean;
  editLabel?: string;
  editIcon?: string;
  onEdit?: () => void;
  isUpdateAvatar?: boolean;
  identity?: string;
  showLiveStream?: boolean;
};

export type ProfileHeaderCoverProps = {
  identity?: string;
  image: string;
  imageId?: string;
  left: number;
  top: string | number;
  alt: string;
  canEdit?: boolean;
  onEdit?: boolean;
  isUpdateAvatar?: boolean;
};

export type UpdateProfileCoverAction = LocalAction<
  {
    identity: string;
    position: { x: number; y: number };
    image: string;
    file?: File;
    photoIdentity?: string;
  },
  {
    onFailure?: () => void;
    onSuccess?: (err?: string) => void;
  }
>;

export type UpdateProfileUserAvatarProps = {
  avatar: File;
  avatar_crop: string;
  userId: string;
  identity: string;
  image_id?: number;
};

export type UpdateProfileUserAvatarMeta = {
  onFailure?: () => void;
  onSuccess?: (err?: string) => void;
};

export type EmailNotificationSettingItem = {
  module_id: string;
  phrase: string;
  value: 1 | 0;
  var_name: string;
};

export type ProfileMenuItem = {
  phrase: string;
  default: number;
  options: { label: string; value: number }[];
};

export type ProfilePrivacyItem = {
  module_id: string;
  var_name: string;
  value: number;
  phrase: string;
  options: { label: string; value: number }[];
};

export type SharingPrivacyItem = {
  module_id: string;
  phrase: string;
  var_name: string;
  value: number;
  custom_id: string;
  options: { label: string; value: number }[];
};

export type Currency = {
  id: string;
  symbol: string;
  name: string;
  format: string;
  is_default: string;
  code: string;
  currency_id: string;
};

export type Language = {
  id: string;
  language_code: string;
  flag_id: string;
  direction: string;
  is_default: boolean;
  name: string;
};

export interface TimeZone {
  id: number;
  name: string;
  diff_from_gtm: string;
}

export type AccountSettings = {
  user_name: string;
  full_name: string;
  last_name: string;
  first_name: string;
  email: string;
  language_id: string;
  timezone_id: string;
  timezone_name: string;
  currency_id: string;
  currency_name: string;
  id: string;
  link: string;
};

export type InvisibleItem = {
  module_id: string;
  phrase: string;
  description: string;
  var_name: string;
  value: string;
};

export type AppState = {
  uiConfig: AppUIConfig;
  entities: {
    user: Record<string, UserItemShape>;
  };
  resourceConfig: {
    user: AppResource;
  };
  invisibleSettings: {
    loaded: boolean;
    error: string;
    data: InvisibleItem;
  };
  accountSettings: {
    data: AccountSettings;
    error: string;
    loaded: boolean;
  };
  paymentSettings: {
    data: AccountSettings;
    error: string;
    loaded: boolean;
  };
  multiFactorAuthSettings: {
    data: MultiFactorAuthSettings[];
    error: string;
    loaded: boolean;
  };
  emailNotificationSettings: {
    data: EmailNotificationSettingItem[];
    error: string;
    loaded: boolean;
  };
  smsNotificationSettings: {
    data: EmailNotificationSettingItem[];
    error: string;
    loaded: boolean;
  };
  notificationSettings: {
    data: EmailNotificationSettingItem[];
    error: string;
    loaded: boolean;
  };
  sharingItemPrivacy: {
    data: SharingPrivacyItem[];
    error: string;
    loaded: boolean;
  };
  profilePrivacy: {
    data: ProfilePrivacyItem[];
    loaded: boolean;
    error: string;
  };
  profileMenu: {
    data: Record<string, ProfileMenuItem>;
    loaded: boolean;
    error: string;
  };
  verify: {
    loading: boolean;
    error?: string;
    success?: boolean;
  };
};
