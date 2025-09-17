/**
 * @type: service
 * name: cookieBackend
 * priority: -10
 */
import JsCookie, { CookieAttributes } from 'js-cookie';
import { CookieBackendConfig } from './types';

export class CookieBackend {
  // default options.
  private readonly config: CookieBackendConfig;

  /**
   * string
   */
  public static readonly configKey: string = 'root.cookie';

  /**
   * constructor
   * @param config
   */
  constructor(config: Partial<CookieBackendConfig>) {
    this.config = Object.assign(
      {
        prefix: process.env.MFOX_COOKIE_PREFIX,
        attributes: {
          SameSite: 'Strict',
          path: '/'
        }
      },
      config
    );
  }

  /**
   * callback hook
   */
  public bootstrap(): void {}

  /**
   *
   * @param {string} name  - get cookie by name
   * @returns
   */
  public get(name: string): string {
    return JsCookie.get(this.toKey(name));
  }

  /**
   *
   * @param {string} name -  get actual save key
   * @returns
   */
  public toKey(name: string): string {
    return this.config.prefix + name;
  }

  /**
   * set cookie by name
   * @param name - string
   * @param value - string
   * @param attributes
   */
  public set(
    name: string,
    value: string,
    attributes?: Partial<CookieAttributes>
  ): void {
    JsCookie.set(
      this.toKey(name),
      value,
      attributes
        ? Object.assign(this.config.attributes, attributes)
        : this.config.attributes
    );
  }

  public getInt(name: string): number {
    return parseInt(JsCookie.get(this.toKey(name)), 10);
  }

  /**
   * get Json value storage in cookie
   * @param {string} name - cookie name
   * @returns
   */
  public getJSON<T = unknown>(name: string): T {
    return JsCookie.getJSON(this.toKey(name));
  }

  /**
   * get actual cookie key
   * @param {string} name - cookie name to remove
   */
  public remove(name: string): void {
    JsCookie.remove(this.toKey(name));
  }
}

export default CookieBackend;
