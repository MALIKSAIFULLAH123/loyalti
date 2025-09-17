import { useWindowSize } from '@metafox/framework';
import React from 'react';

const DEFAULT_TOP_POS = 114; // fix for flaky screen.

export default function useListContentHeight(
  listRef: React.MutableRefObject<HTMLDivElement>
) {
  const [, windowHeight] = useWindowSize();
  const [listHeight, setListHeight] = React.useState<number>(0);
  const [topPos, setTopPos] = React.useState<number>(DEFAULT_TOP_POS);

  React.useEffect(() => {
    if (listRef.current) {
      setTopPos(listRef.current.getBoundingClientRect().top);
    }
  }, [listRef]);

  React.useEffect(() => {
    setListHeight(windowHeight - topPos);
  }, [topPos, windowHeight]);

  return listHeight;
}
