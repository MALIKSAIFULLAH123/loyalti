/**
 * @type: skeleton
 * name: notification.itemView.mainCard.skeleton
 */
import { ItemMedia, ItemText, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <Skeleton variant="circular" width={48} height={48} />
      </ItemMedia>
      <ItemText>
        <Skeleton variant="text" />
        <Skeleton variant="text" width={60} />
      </ItemText>
    </ItemView>
  );
}
