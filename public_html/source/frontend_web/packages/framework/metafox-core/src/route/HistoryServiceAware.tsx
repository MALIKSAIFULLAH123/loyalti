import { IS_ADMINCP, useGlobal } from '@metafox/framework';
import React, { useEffect, useRef } from 'react';
import { useLocation } from 'react-router';
import { useNavigate } from 'react-router-dom';

const LIMIT = 3000; // 300 seconds
const INTERVAL = 400;

export const useLocationHashAware = (hash: string): void => {
  const hashRef = React.useRef<string>(hash ? hash.substring(1) : null);
  const countRef = React.useRef<number>(0);

  React.useEffect(() => {
    hashRef.current = hash ? hash.substring(1) : undefined;
    countRef.current = 0;
  }, [hash]);

  const followHash = React.useCallback(() => {
    if (!hashRef.current || countRef.current > LIMIT) return;

    countRef.current = countRef.current + INTERVAL;

    const anchor = document.getElementById(hashRef.current);

    if (anchor) {
      anchor.scrollIntoView({ behavior: 'auto' });
      hashRef.current = undefined;
    }
  }, []);

  React.useEffect(() => {
    /**
     * todo:
     * Work in process, process to search element in a large form settings
     * SCROLL behavior conflict.
     */
    if (!IS_ADMINCP) return;

    window.setInterval(followHash, INTERVAL);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);
};

const HistoryServiceAware = () => {
  const mn = useGlobal();
  const location = useLocation();
  const navigate = useNavigate();

  const { dispatch, usePrevious } = mn;
  const { pathname: _pathname, key, state } = location;

  // does not start with pathname to prevent dispatch redux action for initial load
  const prevUrl = useRef<string>();
  const pathname = state?.as || _pathname;
  const prevStateUrl = usePrevious(pathname);
  const goSmartBack = React.useCallback(
    url => {
      if (prevStateUrl) {
        navigate(-1);
      } else {
        navigate(url || '/');
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [pathname, prevStateUrl]
  );

  useEffect(() => {
    // click over the same link any time should be reload
    if (prevUrl.current === pathname && !state?.keepScroll) {
      dispatch({ type: '@app/reloadPage', payload: pathname });
    } else {
      prevUrl.current = pathname;
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pathname, key]);

  mn.use({
    navigate,
    location,
    goSmartBack
  });

  return null;
};

export default HistoryServiceAware;
