/**
 * @type: skeleton
 * name: forum_thread.itemView.mainCard.skeleton
 */

import { ItemText, ItemView } from '@metafox/ui';
import { Box, Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton(props) {
  return (
    <ItemView {...props}>
      <ItemText>
        <Box sx={{ display: 'flex' }}>
          <Skeleton variant="circular" width={40} height={40} />
          <Box sx={{ ml: 1, flex: 1, minWidth: 0 }}>
            <Skeleton width="100%" />
            <Skeleton width="60%" />
          </Box>
          <Box
            width="100px"
            ml={3}
            sx={{ display: 'flex', alignItems: 'center' }}
          >
            <Box
              sx={{
                mr: 1.5,
                flex: 1,
                minWidth: 0,
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'flex-end'
              }}
            >
              <Skeleton width="100%" />
              <Skeleton width="60%" />
            </Box>
            <Skeleton variant="circular" width={24} height={24} />
          </Box>
        </Box>
      </ItemText>
    </ItemView>
  );
}
