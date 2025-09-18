/**
 * @type: skeleton
 * name: forum.itemView.mainCard.skeleton
 */
import { ItemView } from '@metafox/ui';
import { Box, Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton(props) {
  return (
    <ItemView {...props}>
      <Box
        sx={{ width: '100%', display: 'flex', justifyContent: 'space-between' }}
      >
        <Box flex={1}>
          <Skeleton width="100%" />
          <Skeleton width={160} />
        </Box>
      </Box>
    </ItemView>
  );
}
