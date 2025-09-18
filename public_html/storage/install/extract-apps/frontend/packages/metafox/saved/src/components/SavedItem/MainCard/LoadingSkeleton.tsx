/**
 * @type: skeleton
 * name: saved.itemView.mainCard.skeleton
 * title: Main Card
 */
import {
  ImageSkeleton,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemView
} from '@metafox/ui';
import { Skeleton, Box } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <ImageSkeleton ratio="11" borderRadius={0} />
      </ItemMedia>
      <ItemText>
        <Box>
          <Skeleton width={'100%'} />
        </Box>
        <ItemSummary>
          <Skeleton width={160} />
        </ItemSummary>
        <div>
          <Skeleton width={160} />
        </div>
      </ItemText>
    </ItemView>
  );
}
