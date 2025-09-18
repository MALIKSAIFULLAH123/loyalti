/**
 * @type: skeleton
 * name: sevent.itemView.topic.skeleton
 */
import { ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView testid="skeleton" wrapAs={wrapAs} wrapProps={wrapProps}>
      <Skeleton width={80} />
    </ItemView>
  );
}
