/**
 * @type: service
 * name: useCachedBlockEmpty
 */
import { useGlobal } from '@metafox/framework';
import React from 'react';
import { get, set, isNumber } from 'lodash';

const BLOCK_CACHED = 'blockLoaderCached';
const TTL_KEY = '_expiry_';
// 24 hours
const TTL = 24 * 60 * 60 * 1000;

const useCachedBlockEmpty = (id = 'idBlockName') => {
  const { localStore, usePageParams } = useGlobal();
  const { pageMetaName } = usePageParams();
  const name = `${pageMetaName}_${id}`;

  const dataCached: Record<string, any> =
    localStore.getJSON(BLOCK_CACHED) || {};
  // clear expire cached empty block
  const now = new Date();
  const expiry = get(dataCached, TTL_KEY);

  if (isNumber(expiry) && now.getTime() > expiry) {
    localStore.remove(BLOCK_CACHED);
  }

  const pathCached = name.replace(/\./g, '_');
  const isCached = get(dataCached, pathCached);
  const [cached, setCached] = React.useState(isCached);

  React.useEffect(() => {
    setCached(isCached);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  React.useEffect(() => {
    try {
      set(dataCached, TTL_KEY, now.getTime() + TTL);
      set(dataCached, pathCached, cached || undefined);
      localStore.set(BLOCK_CACHED, JSON.stringify(dataCached));
      // eslint-disable-next-line no-empty
    } catch (err) {}
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [cached]);

  return [cached, setCached];
};

export default useCachedBlockEmpty;
