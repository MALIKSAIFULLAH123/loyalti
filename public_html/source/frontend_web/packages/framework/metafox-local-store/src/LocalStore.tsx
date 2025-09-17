/**
 * @type: service
 * name: localStore
 * description: Wrap web store api in a manager.
 */
import { MFOX_BUILD_TYPE } from '@metafox/framework';

export interface LocalStoreConfig {
  prefix: string;
}

/**
 * Implementation LocalStore service.
 */
export class LocalStore {
  /**
   * keep local store configure
   */
  private readonly config: LocalStoreConfig;

  /**
   * access back to global configure
   */
  public static readonly configKey: string = 'root.localStore';

  /**
   * constructor
   * @param {LocalStoreConfig} config get local store configure
   */
  constructor(config: Partial<LocalStoreConfig>) {
    this.config = Object.assign(
      {
        prefix: 'mfox'
      },
      config
    );
  }
  /**
   * nothing to do.
   */
  public bootstrap() {}

  /**
   * convert to actual key
   * @param {string} name - convert to actual key
   * @returns
   */
  public toKey(name: string): string {
    return `${this.config.prefix}.${MFOX_BUILD_TYPE}.${name}`;
  }

  public get(name: string): string {
    return localStorage.getItem(this.toKey(name)) || '';
  }

  /**
   * set name and value
   * @param {string} name - get number by name
   * @returns
   */
  public set(name: string, value: string | number): void {
    if (null === value || undefined === value) {
      return localStorage.removeItem(this.toKey(name));
    }

    return localStorage.setItem(this.toKey(name), value.toString());
  }

  /**
   * get number by name
   * @param {string} name - get number by name
   * @returns
   */
  public getInt(name: string): number {
    const v = localStorage.getItem(this.toKey(name));

    return v ? parseInt(v, 10) : 0;
  }

  /**
   * get local store item by name
   * @param {string} name - string
   * @returns
   */
  public getJSON<T = unknown>(name: string): T {
    const v = localStorage.getItem(this.toKey(name));

    return v ? JSON.parse(v) : undefined;
  }

  /**
   * remove local store by name
   * @param {string} name - string
   */
  public remove(name: string): void {
    localStorage.removeItem(this.toKey(name));
  }

  /**
   * clear all local storage in the same domain
   */
  public clear(): void {
    localStorage.clear();
  }
}

export default LocalStore;
