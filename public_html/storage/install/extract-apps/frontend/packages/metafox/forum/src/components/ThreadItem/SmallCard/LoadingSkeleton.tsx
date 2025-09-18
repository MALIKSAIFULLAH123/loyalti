/**
 * @type: skeleton
 * name: forum_thread.itemView.smallCard.skeleton
 */

import { ItemText, ItemView } from '@metafox/ui';
import { Box, Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton(props) {
  return (
    <ItemView {...props}>
      <ItemText>
        <Box sx={{ display: 'flex' }}>
          <Skeleton variant="circular" width={32} height={32} />
          <Box sx={{ ml: 1, flex: 1, minWidth: 0 }}>
            <Skeleton width="100%" />
            <Skeleton width="60%" />
          </Box>
        </Box>
      </ItemText>
    </ItemView>
  );
}
