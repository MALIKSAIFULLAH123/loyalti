/**
 * @type: skeleton
 * name: marketplace.itemView.smallCard.skeleton
 */
import {
  ImageSkeleton,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import * as React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ImageSkeleton ratio="11" />
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" />
        </ItemTitle>
        <Skeleton variant="text" width={80} />
        <ItemSummary>
          <Skeleton variant="text" width={140} />
        </ItemSummary>
      </ItemText>
    </ItemView>
  );
}
