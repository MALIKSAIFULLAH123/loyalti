/**
 * @type: skeleton
 * name: group_announcement.itemView.mainCard.skeleton
 * chunkName: group
 */

import { ItemView } from '@metafox/ui';
import { Skeleton, Box } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <Box sx={{ display: 'flex' }}>
        <Skeleton sx={{ mr: 1 }} variant="circular" width={40} height={40} />
        <Box>
          <Skeleton variant="text" width={120} />
          <Skeleton variant="text" width={70} />
        </Box>
      </Box>
      <Box>
        <Skeleton variant="text" />
        <Skeleton variant="text" />
        <Skeleton variant="text" />
      </Box>
    </ItemView>
  );
}
