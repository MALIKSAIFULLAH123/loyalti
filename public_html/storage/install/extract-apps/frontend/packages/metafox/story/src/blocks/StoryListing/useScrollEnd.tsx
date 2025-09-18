import { debounce } from 'lodash';
import React from 'react';

const THRESHOLD = 200;

export default function useScrollEnd(
  cb?: () => void,
  scrollRef?: React.MutableRefObject<HTMLElement>
) {
  React.useEffect(() => {
    const node = scrollRef?.current;

    if (!cb || !node) {
      return () => void 0;
    }

    const handle = () => {
      if (THRESHOLD > node.scrollWidth - node.scrollLeft - node.clientWidth) {
        cb();
      }
    };

    const bounce = debounce(handle, 200, { leading: true });

    node.addEventListener('scroll', bounce);

    return () => node.removeEventListener('scroll', bounce);

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [cb]);

  return cb;
}
