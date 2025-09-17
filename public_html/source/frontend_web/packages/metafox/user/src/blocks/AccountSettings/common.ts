import { AccountSettings } from '@metafox/user';

export interface SettingBlockProps {
  title: string;
  loaded: boolean;
  data: AccountSettings;
}
