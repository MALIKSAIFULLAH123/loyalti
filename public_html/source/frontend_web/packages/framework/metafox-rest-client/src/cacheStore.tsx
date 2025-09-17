const store: Record<string, { ttl: number; value: any }> = {};

/**
 * Set cache value
 * <code>
 *  cacheStore(30000, 'datagrid', 'user.browse',{columns: [],}); // output object or undefined
 * </code>
 * Get cache value
 * <code>
 *  cacheStore(30000, 'datagrid', 'user.browse'); // output object or undefined
 * </code>
 *
 * Get or set cache
 *
 * @param {Number} ttl - Time to life millisecond
 * @param {String} prefix - Prefix key string. Example: datagrid, menu, category
 * @param {String} name - Prefix key string. Etc: blog/1, video/2
 * @param {String} data - value to set to cache
 * @returns
 */
const cacheStore = <T extends unknown>(
  ttl: number,
  prefix: string = '',
  name: string,
  data?: string
): T | undefined => {
  if (!ttl || !prefix || !name) return undefined;

  const id = `${prefix}::${name}`;

  if (data !== undefined) {
    store[id] = { ttl: new Date().getTime() + ttl, value: data };
  } else {
    if (!store[id]) {
      return undefined;
    }

    if (store[id].ttl < new Date().getTime()) {
      delete store[id];

      return undefined;
    }

    return store[id].value;
  }
};

export const removeCacheStore = (prefix: string, name: string) => {
  const id = `${prefix}::${name}`;
  delete store[id];
};

export default cacheStore;
