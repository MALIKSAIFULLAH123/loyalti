/**
 * @type: service
 * name: useFetchItems
 */
import { useGlobal } from '@metafox/framework';
import { isEqual } from 'lodash';
import { stringify } from 'query-string';
import React, { useEffect } from 'react';
import { FetchDataConfig, FetchDataResult } from './types';

// store cached data
const cached: Record<string, any> = {};

export default function useFetchItems<T = any[]>({
  dataSource,
  pageParams: params,
  data,
  cache,
  allowRiskParams,
  normalize,
  key
}: FetchDataConfig<T[]>) {
  const { apiClient, dispatch, compactData, compactUrl } = useGlobal();
  const mounted = React.useRef<boolean>(true);
  const [pageParams, setPageParams] = React.useState(params);

  const [result, setResult] = React.useState<FetchDataResult<T[]>>([
    data,
    true,
    null,
    null
  ]);

  useEffect(() => {
    if (!isEqual(params, pageParams)) {
      setPageParams(params);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [params]);

  useEffect(() => {
    mounted.current = true;

    const fetchData = async () => {
      const params = allowRiskParams
        ? pageParams
        : compactData(dataSource.apiParams, pageParams);
      const id = `${dataSource.apiUrl}?${stringify(params)}`;

      // do not remember forever
      if (cache && cached[id]) {
        setResult([cached[id], false, null]);

        return;
      }

      setResult(prev => (prev[1] ? prev : [data, true, null]));

      apiClient
        .request({
          url: compactUrl(dataSource.apiUrl, pageParams),
          method: 'GET',
          params
        })
        .then(res => {
          const { data, meta } = res.data;
          const result = Array.isArray(data) ? data : [];

          if (cache) {
            // check cache options
            cached[id] = result;
          }

          if (normalize) {
            dispatch({ type: '@normalize', payload: result });
          }

          if (mounted.current) {
            setResult([result, false, null, meta]);
          }
        })
        .catch(error => {
          if (mounted) {
            setResult([[], false, error]);
          }
        })
        .catch(void 0);
    };

    if (dataSource?.apiUrl) fetchData();

    return () => {
      mounted.current = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pageParams, dataSource?.apiUrl, key]); // check pageParams is actual change

  return result;
}
