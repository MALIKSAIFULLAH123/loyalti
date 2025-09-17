import React from 'react';
import useWindowSize from './useWindowSize';

interface FixedRectValue {
  width: number;
  top: number;
  style: React.CSSProperties;
}

export default function useFixedRect(outerRef: React.RefObject<HTMLElement>) {
  const [state, setState] = React.useState<FixedRectValue>({
    width: 0,
    top: 0,
    style: {}
  });

  const size = useWindowSize();

  React.useEffect(() => {
    if (outerRef.current && document.body.getClientRects().item) {
      const rect = outerRef.current.getBoundingClientRect();
      const bodyRect = document.body.getClientRects().item(0);
      const top = rect.top - bodyRect.top;
      const width = rect.width;

      setState({
        width,
        top,
        style: {
          position: 'fixed',
          width: `${width}px`,
          top: `${top}px`,
          bottom: 0 // use bottom = 0 is better. height: `calc(100vh - ${top}px)`,
        }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [outerRef?.current, size]);

  return state;
}
