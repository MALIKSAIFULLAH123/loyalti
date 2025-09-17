/**
 * @type: service
 * name: ScrollToTopOnMount
 */
import React, { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { useGlobal, HistoryState } from '@metafox/framework';

export default function ScrollToTopOnMount() {
  const { state } = useLocation<HistoryState>();
  const [triggerScroll, setTriggerScroll] = React.useState(false);

  const mn = useGlobal();

  const triggerScrollTop = React.useCallback(() => {
    setTimeout(() => setTriggerScroll(true), 200);
  }, []);

  // trigger scroll handle when suitable time to scroll top
  React.useEffect(() => {
    mn.use({
      triggerScrollTop
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // don't use pathname for dependency because somecase will scroll top too early
  useEffect(() => {
    if (!triggerScroll) return;

    setTriggerScroll(false);

    if (state?.keepScroll) return;

    if (state?.asModal) return;

    const positionY = Number.parseInt(localStorage.getItem('positionY'));

    if (positionY) {
      window.scrollTo({ left: 0, top: positionY, behavior: 'smooth' });
      window.localStorage.removeItem('positionY');
    } else if ('function' === typeof window?.scrollTo) {
      window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [triggerScroll]);

  return null;
}
