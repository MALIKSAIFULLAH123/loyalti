import { AppUIConfig } from '@metafox/framework';

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

export type AppState = {
  uiConfig: AppUIConfig;
  entities: any;

  paymentSettings: {
    data: AccountSettings;
    error: string;
    loaded: boolean;
  }
 
};