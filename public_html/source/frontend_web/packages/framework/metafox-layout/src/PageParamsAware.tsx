import { HistoryState, useGlobal } from '@metafox/framework';
import { useMemo } from 'react';
import { useLocation } from 'react-router-dom';

export default function PageParamAware() {
  const { use, usePageParams, getPageParams, dispatch } = useGlobal();
  const location = useLocation<HistoryState>();
  const { key, state } = location;
  const pageParams = usePageParams();

  useMemo(() => {
    if (state?.asModal && getPageParams) return;

    use({ getPageParams: () => pageParams });
    dispatch({ type: '@PageParamAware/DONE', payload: pageParams });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pageParams, key]);

  return null;
}
