import useScrollRef from '@metafox/layout/useScrollRef';
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

export default function useHasScroll(init) {
  const scrollRef = useScrollRef();
  const [mounted, setMounted] = React.useState(false);
  const [hasScroll, setHasScroll] = React.useState(init);
  const node = scrollRef.current;

  const checkScrollExist = React.useCallback(() => {
    let data;

    if (node) {
      data = node.scrollHeight > node.clientHeight;
    } else {
      data = getDocHeight() > window.innerHeight;
    }

    setHasScroll(data);

    return data;
  }, [node]);

  React.useEffect(() => {
    setMounted(true);

    return () => setMounted(false);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    // state mounted for sync scrollRef
    if (!mounted) return;

    checkScrollExist();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [mounted]);

  return [hasScroll, checkScrollExist];
}
