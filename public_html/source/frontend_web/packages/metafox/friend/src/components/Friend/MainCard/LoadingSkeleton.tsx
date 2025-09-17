/**
 * @type: skeleton
 * name: friend.itemView.mainCard.skeleton
 */
import {
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <Skeleton variant="circular" width={80} height={80} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" />
        </ItemTitle>
        <ItemSummary>
          <Skeleton variant="text" width="120px" />
        </ItemSummary>
      </ItemText>
    </ItemView>
  );
}
