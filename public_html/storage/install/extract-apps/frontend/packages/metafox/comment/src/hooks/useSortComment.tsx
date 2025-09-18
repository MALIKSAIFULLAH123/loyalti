/**
 * @type: service
 * name: useSortComment
 */
import { useGlobal } from '@metafox/framework';
import React from 'react';
import { SORT_RELEVANT, SortTypeValue } from '@metafox/comment';

export default function useSortComment(sortDefault?: SortTypeValue) {
  const { getSetting, usePrevious, usePageParams } = useGlobal();

  const { comment_id } = usePageParams();
  const prevCommentId = usePrevious(comment_id);

  const sortSetting: SortTypeValue = getSetting('comment.sort_by');
  const [sortType, setSortType] = React.useState<SortTypeValue>(
    sortDefault || (comment_id ? SORT_RELEVANT : sortSetting)
  );

  React.useEffect(() => {
    if (comment_id && sortType && sortType !== SORT_RELEVANT) {
      setSortType(SORT_RELEVANT);
    }

    if (!comment_id && prevCommentId) {
      setSortType(sortDefault || sortSetting);
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [comment_id]);
  const [loading, setLoading] = React.useState(false);

  return [sortType, setSortType, loading, setLoading];
}
