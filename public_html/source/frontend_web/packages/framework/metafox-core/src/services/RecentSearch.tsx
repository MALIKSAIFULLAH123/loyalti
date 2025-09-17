/**
 * @type: service
 * name: recentSearch
 */

import { Manager } from '@metafox/framework';
import { uniq } from 'lodash';

type TRecentSearchConfig = {
  limit: number;
  storeKey: string;
};

export default class RecentSearch {
  config: TRecentSearchConfig;

  manager: Manager;

  /**
   * + Manager
   */
  public static readonly configKey: string = 'recentSearch';

  constructor(config: Partial<TRecentSearchConfig>) {
    this.config = Object.assign(
      {
        limit: 20,
        storeKey: 'recentSearch'
      },
      config
    );
  }

  public bootstrap(manager: Manager) {
    this.manager = manager;
  }

  public get(): string[] {
    const { storeKey } = this.config;
    const localStore = this.manager.localStore;

    if (!localStore) {
      return [];
    }

    const data: string[] = localStore.getJSON(storeKey);

    return Array.isArray(data) ? data : [];
  }

  public add(x: string): void {
    const { storeKey } = this.config;
    const localStore = this.manager.localStore;

    if (!localStore) {
      return;
    }

    let data: string[] = localStore.getJSON(storeKey);

    if (!Array.isArray(data)) data = [];

    data.unshift(x);
    data = uniq(data);

    localStore.set(storeKey, JSON.stringify(data));
  }

  public remove(x: string): string[] {
    const { storeKey } = this.config;
    const localStore = this.manager.localStore;

    if (!localStore) {
      return [];
    }

    let data: string[] = localStore.getJSON(storeKey);

    if (!Array.isArray(data)) return [];

    data = data.filter(v => v !== x);

    localStore.set(storeKey, JSON.stringify(data));

    return data;
  }

  public clean(): void {
    const { storeKey } = this.config;
    const localStore = this.manager.localStore;

    if (localStore) {
      localStore.remove(storeKey);
    }
  }
}
