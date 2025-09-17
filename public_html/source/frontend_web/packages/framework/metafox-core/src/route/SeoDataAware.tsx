import { useLocation } from 'react-router';
import { useGlobal } from '../hooks';
import React from 'react';
import { LOAD_PAGE_META } from '../constants';
import qs from 'query-string';

const SeoDataAware = () => {
  const { state, pathname: _pathname, search: _search } = useLocation();
  const { dispatch } = useGlobal();
  const pathname = state?.as || _pathname;
  const queryParams = _search ? qs.parse(_search.replace(/^\?/, '')) : {};
  const href = `${pathname}${_search}`;
  const cancelController = new AbortController();

  React.useEffect(
    () => {
      dispatch({
        type: LOAD_PAGE_META,
        payload: {
          pathname,
          queryParams,
          href,
          signal: cancelController.signal
        }
      });

      return () => cancelController.abort();
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [href]
  );

  return null;
};

export default SeoDataAware;
