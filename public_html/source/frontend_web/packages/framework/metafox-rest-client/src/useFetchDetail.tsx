/**
 * @type: service
 * name: useFetchDetail
 */
import { useGlobal } from '@metafox/framework';
import { useEffect, useState } from 'react';
import cacheStore from './cacheStore';
import { FetchDataConfig, FetchDataResult } from './types';

export default function useFetchDetail<T = Record<string, any>>({
  dataSource,
  pageParams,
  preventReload,
  data: _data,
  allowRiskParams,
  cachePrefix,
  cacheKey,
  ttl,
  forceReload = true,
  normalize
}: FetchDataConfig<T>) {
  const { apiClient, compactUrl, compactData, dispatch } = useGlobal();
  const pageParamsJsonString = JSON.stringify(pageParams);

  const checkCache = ttl && cachePrefix && cacheKey;

  const cached = cacheStore<T>(ttl, cachePrefix, cacheKey);
  const data = cached ?? _data;

  const [result, setResult] = useState<FetchDataResult<T>>([
    data,
    !data || forceReload,
    undefined,
    undefined,
    undefined
  ]);

  useEffect(() => {
    let mounted = true;

    if (!dataSource?.apiUrl) return;

    const fetchData = async () => {
      if (preventReload && result[0]) return;

      setResult([data, true, undefined, undefined]);

      const response = await apiClient
        .request({
          url: compactUrl(dataSource.apiUrl, pageParams),
          method: dataSource.apiMethod ?? 'GET',
          params: allowRiskParams
            ? pageParams
            : compactData(dataSource.apiParams, pageParams)
        })
        .catch(error => {
          if (mounted) {
            setResult([undefined, false, error, undefined]);
          }
        })
        .catch(void 0);

      if (mounted && response) {
        const data = response.data?.data;
        const meta = response.data?.meta;
        const message = response.data?.message;

        if (checkCache) {
          cacheStore(ttl, cachePrefix, cacheKey, data);
        }

        if (normalize) {
          dispatch({ type: '@normalize', payload: data });
        }

        setResult([data, false, undefined, message, meta]);
      }
    };

    if (!data || forceReload) {
      fetchData();
    } else if (data && cached) {
      setResult([data, false, undefined, undefined, undefined]);
    }

    return () => {
      mounted = false;
    };

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pageParamsJsonString, dataSource?.apiUrl, cacheKey]);

  return result;
}
