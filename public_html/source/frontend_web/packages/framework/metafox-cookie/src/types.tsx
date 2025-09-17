import { CookieAttributes } from 'js-cookie';

export interface CookieBackendConfig extends CookieAttributes {
  prefix: string;
  attributes?: CookieAttributes;
}
