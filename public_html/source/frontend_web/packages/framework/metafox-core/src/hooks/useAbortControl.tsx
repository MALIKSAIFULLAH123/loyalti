import { uniqueId } from 'lodash';
import { useEffect, useMemo } from 'react';
import { ABORT_CONTROL_CANCEL } from '../constants';
import useGlobal from './useGlobal';

/**
 * handle abort control
 * @returns {string}
 */
export default function useAbortControl(pagingId?: string): string {
  const { dispatch } = useGlobal();
  const abortId = useMemo(() => {
    return uniqueId('abort');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pagingId]);

  useEffect(() => {
    return () => {
      dispatch({ type: ABORT_CONTROL_CANCEL, payload: { abortId } });
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pagingId]);

  return abortId;
}
