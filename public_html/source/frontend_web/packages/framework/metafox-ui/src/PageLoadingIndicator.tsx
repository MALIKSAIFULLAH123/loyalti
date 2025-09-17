/**
 * @type: service
 * name: PageLoadingIndicator
 */
import { useGlobal } from '@metafox/framework';
import { sleep } from '@metafox/utils';
import { styled } from '@mui/material';
import React from 'react';

const IndicatorRoot = styled('div', {
  name: 'PageLoadingIndicator',
  slot: 'Root'
})({ pointerEvents: 'none' });

const ProgressBar = styled('div', {
  name: 'PageLoadingIndicator',
  slot: 'ProgressBar'
})({
  zIndex: 1400,
  position: 'fixed',
  top: 0,
  right: 0,
  left: 0,
  width: '100%',
  height: '2px',
  backgroundColor: 'red',
  overflow: 'hidden',
  transition: 'all 200ms ease 0s',
  display: 'none'
});

export default function PageLoadingIndicator() {
  const estimate = 2000; // estimate total loading time
  const interval = 50; // seconds to update;
  const [status, setStatus] = React.useState<
    'start' | 'sending' | 'loading' | 'finish' | 'none'
  >('none');
  const timer = React.useRef<number>(0);
  const progress = React.useRef<number>(0);
  const ref = React.useRef<HTMLDivElement>();

  const mn = useGlobal();

  // call one on first time
  // don't change to useEffect because it must be init
  // before MainRoutes going to mount
  React.useMemo(() => {
    mn.use({
      setRouteLoading: (loading: boolean) =>
        sleep(300).then(() => setStatus(loading ? 'start' : 'finish'))
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const updateStyle = () => {
    if (ref.current?.setAttribute) {
      const x = progress.current / estimate - 1;

      ref.current.setAttribute(
        'style',
        `display: block; transform:translate3d(${
          0 < x ? 0 : x * 100
        }%, 0px, 0px)`
      );
    }
  };

  const finishAnimation = React.useCallback(() => {
    if (timer.current) {
      window.clearTimeout(timer.current);
    }

    timer.current = window.setInterval(() => {
      progress.current += interval * 4;

      if (progress.current > estimate) {
        setStatus('none');
      }

      updateStyle();
    }, interval);
  }, []);

  const startAnimation = React.useCallback(() => {
    progress.current = 0.25 * estimate;

    // clear timer
    if (timer.current) {
      window.clearTimeout(timer.current);
    }

    timer.current = window.setInterval(() => {
      progress.current += interval;
      updateStyle();
    }, interval);
  }, []);

  React.useEffect(() => {
    if ('start' === status) {
      startAnimation();
    } else if ('finish' === status) {
      finishAnimation();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [status]);

  if ('none' === status) {
    return null;
  }

  return (
    <IndicatorRoot data-testid="loadingIndicator">
      <ProgressBar ref={ref} />
    </IndicatorRoot>
  );
}
