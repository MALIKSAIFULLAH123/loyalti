import useScrollRef from '@metafox/layout/useScrollRef';
import { debounce } from 'lodash';
import React from 'react';

function getDocHeight() {
  const x = document;

  return Math.max(
    x.body.scrollHeight,
    x.documentElement.scrollHeight,
    x.body.offsetHeight,
    x.documentElement.offsetHeight,
    x.body.clientHeight,
    x.documentElement.clientHeight
  );
}

const THRESHOLD = 200;

export default function useScrollEnd(cb?: () => void) {
  const scrollRef = useScrollRef();

  React.useEffect(() => {
    const node = scrollRef.current;

    if (!cb) {
      return () => void 0;
    }

    const handle = node
      ? () => {
          if (
            THRESHOLD >
            node.scrollHeight - node.scrollTop - node.clientHeight
          ) {
            cb();
          }
        }
      : () => {
          if (window.scrollY + window.innerHeight * 2 > getDocHeight()) {
            cb();
          }
        };

    const bounce = debounce(handle, 200, { leading: true });

    if (node) {
      node.addEventListener('scroll', bounce);

      return () => node.removeEventListener('scroll', bounce);
    }

    window.addEventListener('scroll', bounce);

    return () => window.removeEventListener('scroll', bounce);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [cb]);

  return cb;
}
