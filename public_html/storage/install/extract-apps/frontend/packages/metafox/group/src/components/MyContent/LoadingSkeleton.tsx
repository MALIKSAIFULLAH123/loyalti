import { ItemView } from '@metafox/ui';
import { Box, Skeleton } from '@mui/material';
import * as React from 'react';

export function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView testid="loadingSkeleton" wrapAs={wrapAs} wrapProps={wrapProps}>
      <Box sx={{ p: 2, display: 'flex' }}>
        <Skeleton variant="circular" width={40} height={40} />
        <Box sx={{ pl: 1 }}>
          <Skeleton variant="text" component="div" />
          <Skeleton variant="text" width={120} />
        </Box>
      </Box>
      <Box sx={{ width: '50%' }}>
        <Skeleton variant="text" />
        <Skeleton variant="text" />
        <Skeleton variant="text" />
      </Box>
    </ItemView>
  );
}

export default LoadingSkeleton;
