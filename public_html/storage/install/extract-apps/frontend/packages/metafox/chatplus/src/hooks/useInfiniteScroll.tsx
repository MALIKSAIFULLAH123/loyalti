import { useScrollRef } from '@metafox/framework';
import { useEffect, useState } from 'react';

const THRESHOLD_SCROLL = 20;

const useInfiniteScroll = (
  refProps,
  callback
): [boolean, React.Dispatch<React.SetStateAction<boolean>>] => {
  const [isFetching, setIsFetching] = useState(false);
  const ref = useScrollRef();

  const target = refProps ? refProps.current : ref && ref.current;

  useEffect(() => {
    if (target) target.addEventListener('scroll', handleScroll);

    return () => {
      if (target) target.removeEventListener('scroll', handleScroll);
    };
  }, [target]);

  useEffect(() => {
    if (!isFetching) return;

    callback();
  }, [isFetching]);

  if (!ref || !ref.current) return [isFetching, setIsFetching];

  function handleScroll() {
    if (
      target.clientHeight + target.scrollTop + THRESHOLD_SCROLL <
        target.scrollHeight ||
      isFetching
    )
      return;

    setIsFetching(true);
  }

  return [isFetching, setIsFetching];
};

export default useInfiniteScroll;
