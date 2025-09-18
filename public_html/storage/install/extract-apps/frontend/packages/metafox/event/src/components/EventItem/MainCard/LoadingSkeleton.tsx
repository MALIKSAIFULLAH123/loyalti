/**
 * @type: skeleton
 * name: event.itemView.mainCard.skeleton
 */

import { ImageSkeleton, ItemMedia, ItemText, ItemView } from '@metafox/ui';
import { Box, Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="">
      <ItemMedia>
        <ImageSkeleton ratio="165" borderRadius={0} />
      </ItemMedia>
      <ItemText>
        <Box sx={{ pt: 1.5 }}>
          <Skeleton width={'100%'} />
          <Skeleton width={'100%'} />
          <Skeleton width={160} />
          <Skeleton width={160} />
        </Box>
      </ItemText>
    </ItemView>
  );
}
