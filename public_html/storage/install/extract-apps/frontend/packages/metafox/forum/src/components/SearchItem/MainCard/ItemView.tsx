import { useGlobal } from '@metafox/framework';
import React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

export default function SearchItemMainCard(props) {
  const { usePageParams, jsxBackend } = useGlobal();
  const { item_type } = usePageParams();
  const ItemView = jsxBackend.get(
    item_type === 'forum_post'
      ? 'forum_post.itemView.mainCard'
      : 'forum_thread.itemView.mainCard'
  );

  return <ItemView {...props} />;
}
SearchItemMainCard.LoadingSkeleton = LoadingSkeleton;
SearchItemMainCard.displayName = 'ForumSearchItem(MainCard)';
