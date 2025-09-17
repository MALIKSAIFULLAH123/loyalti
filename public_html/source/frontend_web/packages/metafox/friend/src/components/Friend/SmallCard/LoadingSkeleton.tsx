/**
 * @type: skeleton
 * name: friend.itemView.smallCard.skeleton
 */
import { ItemMedia, ItemText, ItemTitle, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <Skeleton variant="avatar" width={48} height={48} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width="50%" />
        </ItemTitle>
        <Skeleton variant="text" width="120px" />
      </ItemText>
    </ItemView>
  );
}
