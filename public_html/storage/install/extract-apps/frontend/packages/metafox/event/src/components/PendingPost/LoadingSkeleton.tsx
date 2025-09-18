/**
 * @type: skeleton
 * name: event.itemView.pendingPost.skeleton
 */

import { ItemView } from '@metafox/ui';
import { Box, Skeleton } from '@mui/material';
import * as React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView testid="loadingSkeleton" wrapAs={wrapAs} wrapProps={wrapProps}>
      <Box sx={{ display: 'flex' }}>
        <Skeleton variant="circular" width={40} height={40} />
        <Box sx={{ pl: 1, flex: 1, minWidth: 0 }}>
          <Skeleton variant="text" component="div" />
          <Skeleton variant="text" width={120} />
        </Box>
      </Box>
      <Box>
        <Box sx={{ height: 100 }}></Box>
        <Box
          sx={{
            display: 'flex'
          }}
        >
          <Skeleton variant="text" width={100} sx={{ mr: 1 }} />
          <Skeleton variant="text" width={100} />
        </Box>
      </Box>
    </ItemView>
  );
}
