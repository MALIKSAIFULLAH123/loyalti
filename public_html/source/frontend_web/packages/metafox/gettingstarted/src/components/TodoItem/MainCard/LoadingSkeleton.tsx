/**
 * @type: skeleton
 * name: gettingStarted.itemView.todoItem.skeleton
 * chunkName: gettingStared
 */

import { Box, Skeleton } from '@mui/material';
import * as React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <Box width="100%" display="flex" pt={2} ml={3.5}>
      <Skeleton
        width='20px'
        height='20px'
        variant='circular'
        sx={{ mr: 2 }}
      />
      <Skeleton width={'80%'} />
    </Box>
  );
}
