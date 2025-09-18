/**
 * @type: skeleton
 * name: pages.itemView.pendingMainCard.skeleton
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
    <ItemView testid="loading" wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <Skeleton variant="avatar" width={80} height={80} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width={300} />
        </ItemTitle>
        <ItemSummary>
          <Skeleton variant="text" width={120} />
        </ItemSummary>
      </ItemText>
    </ItemView>
  );
}
