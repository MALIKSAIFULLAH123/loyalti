import { useGlobal } from '@metafox/framework';
import * as React from 'react';

export default function CommentListing(props: Record<string, any>) {
  const { useSortComment, CommentList } = useGlobal();

  if (!useSortComment || !CommentList) return null;

  // eslint-disable-next-line react-hooks/rules-of-hooks
  const [sortType, setSortType] = useSortComment();

  return (
    <CommentList sortType={sortType} setSortType={setSortType} {...props} />
  );
}
